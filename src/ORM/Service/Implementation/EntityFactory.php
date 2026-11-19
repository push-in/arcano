<?php

declare(strict_types=1);

namespace Arcano\ORM\Service\Implementation;

use Arcano\ORM\EntityProxyInterface;
use Arcano\ORM\Exception\MapperException;
use Arcano\ORM\Heap\Heap;
use Arcano\ORM\Heap\HeapInterface;
use Arcano\ORM\Heap\Node;
use Arcano\ORM\Relation\HasMany;
use Arcano\ORM\Mapper\Mapper;
use Arcano\ORM\Reference\ReferenceInterface;
use Arcano\ORM\Service\EntityFactoryInterface;
use Arcano\ORM\Service\IndexProviderInterface;
use Arcano\ORM\Service\MapperProviderInterface;
use Arcano\ORM\Service\RelationProviderInterface;
use Arcano\ORM\SchemaInterface;
use Arcano\ORM\Select\LoaderInterface;
use Arcano\ORM\Service\RoleResolverInterface;

/**
 * @internal
 */
final class EntityFactory implements EntityFactoryInterface
{
    /**
     * Per role: resolved role, relation map, mapper, primary key and heap indexes.
     *
     * @var array<string, array{string, \Arcano\ORM\RelationMap, \Arcano\ORM\MapperInterface, string|string[], array}>
     */
    private array $roles = [];

    /** @var array<string, \Closure|false> Data makers per role. */
    private array $dataMakers = [];

    /** @var array<string, \Closure|false> Row makers per role and column list. */
    private array $rowMakers = [];

    /** @var array<class-string, bool> Proxy classes whose entity declares __unset(). */
    private array $customUnset = [];

    public function __construct(
        private HeapInterface $heap,
        private SchemaInterface $schema,
        private MapperProviderInterface $mapperProvider,
        private RelationProviderInterface $relationProvider,
        private IndexProviderInterface $indexProvider,
        private RoleResolverInterface $roleResolver,
    ) {}

    public function make(
        string $role,
        array $data = [],
        int $status = Node::NEW,
        bool $typecast = false,
    ): object {
        $role = $data[LoaderInterface::ROLE_KEY] ?? $role;
        unset($data[LoaderInterface::ROLE_KEY]);

        if ($status === Node::MANAGED) {
            $maker = $this->dataMakers[$role] ??= $this->compileDataMaker($role) ?? false;
            if ($maker !== false && ($entity = $maker($data, $typecast)) !== null) {
                return $entity;
            }
        }

        [$rRole, $relMap, $mapper, $pk, $indexes, $deferReferences] = $this->roles[$role] ??= $this->describe($role);

        $castedData = $typecast ? $mapper->cast($data) : $data;

        if ($status !== Node::NEW) {
            if (\is_array($pk)) {
                $ids = [];
                foreach ($pk as $key) {
                    if (!isset($data[$key])) {
                        $ids = null;
                        break;
                    }
                    $ids[$key] = $data[$key];
                }
            } else {
                $ids = isset($data[$pk]) ? [$pk => $data[$pk]] : null;
            }

            if ($ids !== null) {
                $e = $this->heap->find($rRole, $ids);

                if ($e !== null) {
                    $relations = $relMap->getRelations();
                    $fetched = $mapper->fetchRelations($e);

                    // Get not resolved (references) or not set relations
                    $overwrite = [];
                    foreach ($relations as $name => $_) {
                        if (!\array_key_exists($name, $fetched) || $fetched[$name] instanceof ReferenceInterface) {
                            $overwrite[$name] = true;
                        }
                    }

                    if ($overwrite === []) {
                        return $e;
                    }

                    $node = $this->heap->get($e);
                    \assert($node !== null);

                    // Replace references with actual relation data
                    return $mapper->hydrate($e, $relMap->init(
                        $this,
                        $node,
                        \array_intersect_key($castedData, $overwrite),
                    ));
                }
            }
        }

        $node = new Node($status, $castedData, $rRole);
        $e = $mapper->init($data, $role);

        /** Entity should be attached before {@see RelationMap::init()} running */
        $this->heap->attach($e, $node, $indexes);

        if ($deferReferences) {
            // Proxy entities read unloaded relations from their node on first use.
            $e->__arcano_node = $node;
        }

        return $mapper->hydrate($e, $relMap->init($this, $node, $castedData, $deferReferences));
    }

    /**
     * A closure that turns FETCH_NUM rows of the given root columns into managed
     * entities without the parser, or null when the role needs the generic path
     * (custom mapper or typecast, inheritance, composite key, unusual fields).
     *
     * @internal
     *
     * With `$groupBy` the entities are only listed in the `$groups` argument of the closure.
     *
     * @param list<non-empty-string> $columns
     *
     * @return null|\Closure(iterable<array>): list<object>
     */
    public function rowMaker(string $role, array $columns, ?string $groupBy = null, bool $deferRelations = true): ?\Closure
    {
        $key = $role . "\0" . \implode("\0", $columns) . "\1" . $groupBy . ($deferRelations ? '' : "\2");
        return ($this->rowMakers[$key] ??= $this->compileRowMaker($role, $columns, $groupBy, $deferRelations) ?? false)
            ?: null;
    }

    /**
     * What the compiled makers need for a role and field list, or null when the role
     * needs the generic path (custom mapper or typecast, inheritance, composite key).
     *
     * @return array{string, \Arcano\ORM\RelationMap, string, list<string>, array, array}|null
     */
    private function makerPlan(string $role, array $fields, bool $loaded = false): ?array
    {
        [$rRole, $relMap, $mapper, $pk, $indexes, $deferReferences] = $this->roles[$role] ??= $this->describe($role);
        if (
            !$deferReferences
            || !$this->heap instanceof Heap
            || !\is_string($pk)
            || $rRole !== $role
            || $this->schema->define($role, SchemaInterface::PARENT) !== null
            || ($this->schema->define($role, SchemaInterface::CHILDREN) ?? []) !== []
            // Joined-table subclasses: a row may belong to a subclass the maker doesn't build.
            || $this->schema->getInheritedRoles($role) !== []
            || \count(\array_unique($fields)) !== \count($fields)
            || !\in_array($pk, $fields, true)
        ) {
            return null;
        }
        $rules = $mapper->plainCastRules();
        if ($rules === null) {
            return null;
        }
        $keys = [];
        foreach ($indexes as $key) {
            if (\is_array($key)) {
                if (\count($key) !== 1) {
                    return null;
                }
                $key = \current($key);
            }
            $keys[] = $key;
        }
        $rowPlan = $mapper->rowPlan($fields, $loaded);

        return $rowPlan === null ? null : [$rRole, $relMap, $pk, $keys, $rules, $rowPlan];
    }

    /**
     * For managed data with exactly the column fields of the role: creates the entity
     * like {@see make()} without dispatch. Returns null when the generic path must run
     * (other keys, missing key, already in the heap).
     *
     * @return null|\Closure(array, bool): ?object
     */
    private function compileDataMaker(string $role): ?\Closure
    {
        $mapper = ($this->roles[$role] ??= $this->describe($role))[2];
        if (!$mapper instanceof Mapper) {
            return null;
        }
        $fields = $mapper->columnFields();
        $plan = $this->makerPlan($role, $fields);
        if ($plan === null) {
            return null;
        }
        [$rRole, $relMap, $pk, $keys, $rules] = $plan;
        $hydrate = $mapper->compileRowHydrator($fields);
        if ($hydrate === null) {
            return null;
        }

        $items = [];
        $casters = [];
        $zones = [];
        foreach (\array_intersect_key($rules, \array_flip($fields)) as $field => $rule) {
            $key = \var_export($field, true);
            if ($rule instanceof \DateTimeZone) {
                $zones[$field] = $rule;
                $items[] = "\$d[{$key}] === null or \$d[{$key}] = new \\DateTimeImmutable(\$d[{$key}], \$zones[{$key}]);";
                continue;
            }
            if ($rule instanceof \Closure) {
                $casters[$field] = $rule;
                $items[] = "\$d[{$key}] === null or \$d[{$key}] = \$casters[{$key}](\$d[{$key}]);";
                continue;
            }
            $items[] = "\$d[{$key}] === null or \$d[{$key}] = ({$rule}) \$d[{$key}];";
        }
        $cast = eval("declare(strict_types=1);\nreturn static function (array \$d) use (\$casters, \$zones): array {\n" . \implode("\n", $items) . "\nreturn \$d;\n};");
        $flip = \array_fill_keys($fields, true);
        $count = \count($fields);
        $heap = $this->heap;
        $deferRelations = $relMap->getRelations() !== [];

        return static function (array $data, bool $typecast) use (
            $rRole, $relMap, $pk, $keys, $hydrate, $cast, $flip, $count, $heap, $deferRelations,
        ): ?object {
            if (\count($data) !== $count || !isset($data[$pk]) || \array_diff_key($data, $flip) !== []) {
                return null;
            }
            if ($heap->findKey($rRole, $pk, (string) $data[$pk]) !== null) {
                return null;
            }
            $typecast and $data = $cast($data);
            $node = new Node(Node::MANAGED, $data, $rRole);
            try {
                $entity = $hydrate($data, $node);
            } catch (\TypeError $e) {
                throw new MapperException(
                    "Can't hydrate an entity because property and value types are incompatible.",
                    previous: $e,
                );
            }
            $heap->attachLoaded($entity, $node, $rRole, $data, $keys);
            $deferRelations and $node->deferRelations($relMap);

            return $entity;
        };
    }

    /**
     * @param bool $deferRelations false when the caller sets every relation of the new entities
     */
    private function compileRowMaker(string $role, array $columns, ?string $groupBy, bool $deferRelations): ?\Closure
    {
        // Without deferred relations the caller assigns them all: plain relation properties.
        $plan = $this->makerPlan($role, $columns, !$deferRelations);
        if ($plan === null || ($groupBy !== null && !\in_array($groupBy, $columns, true))) {
            return null;
        }
        [$rRole, $relMap, $pk, $keys, $rules, [$prototype, $assign, $scope]] = $plan;
        $heap = $this->heap;
        $pkIndex = \array_search($pk, $columns, true);

        // Typecast compiled into the array literal: null stays null, as in Typecast::cast().
        $items = [];
        $casters = [];
        $zones = [];
        foreach ($columns as $i => $field) {
            $key = \var_export($field, true);
            $rule = $rules[$field] ?? null;
            if ($rule instanceof \DateTimeZone) {
                // The datetime rule ({@see \Arcano\ORM\Parser\Typecast}) without a call per value.
                $zones[$field] = $rule;
                $items[] = "{$key} => \$row[{$i}] === null ? null : new \\DateTimeImmutable(\$row[{$i}], \$zones[{$key}])";
                continue;
            }
            if ($rule instanceof \Closure) {
                $casters[$field] = $rule;
                $items[] = "{$key} => \$row[{$i}] === null ? null : \$casters[{$key}](\$row[{$i}])";
                continue;
            }
            $items[] = $rule !== null
                ? "{$key} => \$row[{$i}] === null ? null : ({$rule}) \$row[{$i}]"
                : "{$key} => \$row[{$i}]";
        }
        $build = \implode(', ', $items);
        $index = '';
        foreach ($keys as $key) {
            $key = \var_export($key, true);
            $index .= "isset(\$d[{$key}]) and \$index[{$key}][\\is_int(\$k = \$d[{$key}]) ? \$k : (string) \$k] = \$o;\n";
        }
        $pkKey = \var_export($pk, true);
        $roleKey = \var_export($rRole, true);
        $defer = $deferRelations && $relMap->getRelations() !== []
            ? ($scope === null ? "\$n->deferredRelations = \$relMap;\n" : "\$n->deferRelations(\$relMap);\n")
            : '';
        // $groups lists the results by the value of $groupBy, as their nodes have it.
        $group = $heapGroup = $filter = '';
        if ($groupBy !== null) {
            $groupKey = \var_export($groupBy, true);
            $groupIndex = \array_search($groupBy, $columns, true);
            // $only keeps the rows whose group value is one of its keys.
            $filter = "if (\$only !== null) {\n\$g = \$row[{$groupIndex}];\nif (\$g === null || !isset(\$only[\$g])) {\ncontinue;\n}\n}\n";
            $group = "\$groups[\\is_int(\$k = \$d[{$groupKey}]) ? \$k : (string) \$k][] = \$o;\n";
            $heapGroup = "\$groups[(string) (\$heap->get(\$o)?->getData()[{$groupKey}] ?? null)][] = \$o;\n";
        }

        // With public fields the loop runs in the node scope and clones a prepared node
        // instead of calling its constructor for every row.
        $nodePrototype = new Node(Node::MANAGED, [], $rRole, true);
        $newNode = $scope === null
            ? "\$n = clone \$nodePrototype;\n\$n->data = \$d;\n"
            : "\$n = new \\Arcano\\ORM\\Heap\\Node(\\Arcano\\ORM\\Heap\\Node::MANAGED, \$d, {$roleKey}, true);\n";

        // Entities from the heap or without a key take the generic path ($make).
        // Columns appended by the driver (SQL Server paging adds a row number) are dropped.
        $width = \count($columns);
        $make = fn (array $row): object => $this->make(
            $role,
            \array_combine($columns, \count($row) === $width ? $row : \array_slice($row, 0, $width)),
            Node::MANAGED,
            typecast: true,
        );

        // Grouped results are only listed in $groups.
        $append = $groupBy === null ? "\$result[] = \$o;\n" : '';
        $made = $groupBy === null ? '$result' : '\\array_merge([], ...\\array_values($groups ?? []))';

        // One entity per primary key, as the parser gives. A key seen twice is in the heap
        // index by then, so with an indexed key the seen list is built on the first index hit.
        if (\in_array($pk, $keys, true)) {
            $seenInit = "\$seen = null;\n";
            $seenCheck = '';
            $seenHit = "if (\$seen === null) {\n\$seen = [];\nforeach ({$made} as \$r) {\n"
                . "\$k = \$heap->get(\$r)?->getData()[{$pkKey}] ?? null;\n"
                . "\$k === null or \$seen[\\is_int(\$k) ? \$k : (string) \$k] = true;\n}\n}\n"
                . "if (isset(\$seen[\$id])) {\ncontinue;\n}\n\$seen[\$id] = true;\n";
            $seenMade = "\$seen === null or \$seen[\$id] = true;\n";
        } else {
            $seenInit = "\$seen = [];\n";
            $seenCheck = "if (isset(\$seen[\$id])) {\ncontinue;\n}\n\$seen[\$id] = true;\n";
            $seenHit = $seenMade = '';
        }

        // $fresh collects the ids of the entities created here (not taken from the heap),
        // $nodes the heap node of each result.
        $maker = eval(<<<PHP
            declare(strict_types=1);
            return static function (iterable \$rows, ?array &\$fresh = null, ?array &\$nodes = null, ?array &\$groups = null, ?array \$only = null) use (\$heap, \$make, \$prototype, \$relMap, \$nodePrototype, \$casters, \$zones): array {
                \$result = [];
                {$seenInit}                \$storage = \$heap->loadedStorage();
                \$index = &\$heap->loadedIndex({$roleKey});
                try {
                    foreach (\$rows as \$row) {
                        {$filter}\$id = \$row[{$pkIndex}];
                        if (\$id === null) {
                            \$o = \$make(\$row);
                            {$append}                            \$nodes === null or \$nodes[] = \$heap->get(\$o);
                            {$heapGroup}continue;
                        }
                        // Integer keys stay integers: the same array keys without a string per row.
                        \is_int(\$id) or \$id = (string) \$id;
                        {$seenCheck}if (isset(\$index[{$pkKey}][\$id])) {
                            {$seenHit}                            \$o = \$make(\$row);
                            {$append}                            \$nodes === null or \$nodes[] = \$heap->get(\$o);
                            {$heapGroup}continue;
                        }
                        \$d = [{$build}];
                        {$newNode}                        \$o = clone \$prototype;
                        {$assign}\$o->__arcano_node = \$n;
                        \$storage[\$o] = \$n;
                        {$index}{$defer}{$seenMade}\$fresh === null or \$fresh[\\spl_object_id(\$o)] = true;
                        \$nodes === null or \$nodes[] = \$n;
                        {$group}{$append}                    }
                } catch (\\TypeError \$e) {
                    throw new \\Arcano\\ORM\\Exception\\MapperException(
                        "Can't hydrate an entity because property and value types are incompatible.",
                        previous: \$e,
                    );
                }

                return \$result;
            };
            PHP);

        return \Closure::bind($maker, null, $scope ?? Node::class);
    }

    /**
     * @internal
     */
    public function relationMap(string $role): \Arcano\ORM\RelationMap
    {
        return ($this->roles[$role] ??= $this->describe($role))[1];
    }

    /**
     * For new proxies of a role made by {@see rowMaker()}: sets a loaded relation on the node
     * and the entity as {@see attachLoadedRelation()} does. Null when the entity class
     * handles __unset() itself.
     *
     * @internal
     *
     * @return null|\Closure(object, Node, array): void
     */
    public function freshRelationSetter(string $role, string $name, bool $loaded = false): ?\Closure
    {
        [, $relMap, $mapper, , , $proxies] = $this->roles[$role] ??= $this->describe($role);
        $class = $this->schema->define($role, SchemaInterface::ENTITY);
        if (!$proxies || !\is_string($class) || \method_exists($class, '__unset')) {
            return null;
        }
        $relation = $relMap->getRelations()[$name];
        if ($loaded) {
            if (!$mapper instanceof Mapper) {
                return null;
            }
            // Entities of a loaded row maker: the property is declared and still set.
            $assign = $mapper->relationAssigner($name);
            $plain = $relation instanceof HasMany && $relation->collectsArrays();
            return static function (object $entity, Node $node, array $elements) use ($relation, $name, $assign, $plain): void {
                $node->setRelation($name, $elements);
                try {
                    $assign($entity, $plain ? $elements : $relation->collect($elements));
                } catch (\TypeError $e) {
                    throw new MapperException(
                        "Can't hydrate an entity because property and value types are incompatible.",
                        previous: $e,
                    );
                }
            };
        }

        return static function (object $entity, Node $node, array $elements) use ($relation, $name): void {
            $node->setRelation($name, $elements);
            try {
                $entity->__set($name, $relation->collect($elements));
            } catch (\TypeError $e) {
                throw new MapperException(
                    "Can't hydrate an entity because property and value types are incompatible.",
                    previous: $e,
                );
            }
        };
    }

    /**
     * For an entity of a loaded row maker whose relation won't be set: a lazy relation
     * as the default row maker gives.
     *
     * @internal
     */
    public function deferLoaded(string $role, object $entity, Node $node, string $name): void
    {
        [, $relMap, $mapper] = $this->roles[$role] ??= $this->describe($role);
        $mapper->unsetRelation($entity, $name);
        $node->deferRelations($relMap);
    }

    /**
     * Removes entities from the heap.
     *
     * @internal
     */
    public function discard(array $entities): void
    {
        foreach ($entities as $entity) {
            $this->heap->detach($entity);
        }
    }

    /**
     * Sets a loaded relation like {@see RelationMap::init()} does, on a new entity or
     * on a heap entity whose relation is still unresolved.
     *
     * @internal
     */
    public function attachLoadedRelation(
        string $role,
        object $entity,
        string $name,
        array $elements,
        bool $fresh,
        ?Node $node = null,
    ): void {
        [, $relMap, $mapper] = $this->roles[$role] ??= $this->describe($role);
        if (!$fresh) {
            $fetched = $mapper->fetchRelations($entity);
            if (\array_key_exists($name, $fetched) && !$fetched[$name] instanceof ReferenceInterface) {
                return;
            }
        }
        $relation = $relMap->getRelations()[$name];
        ($node ?? $this->heap->get($entity))?->setRelation($name, $elements);
        $value = $relation->collect($elements);

        // A new proxy still has the relation unset: its __set() is what the hydrator ends in.
        if ($fresh && $entity instanceof EntityProxyInterface
            && !($this->customUnset[$entity::class] ??= \method_exists(\get_parent_class($entity), '__unset'))) {
            try {
                $entity->__set($name, $value);
            } catch (\TypeError $e) {
                throw new MapperException(
                    "Can't hydrate an entity because property and value types are incompatible.",
                    previous: $e,
                );
            }
            return;
        }
        $mapper->hydrate($entity, [$name => $value]);
    }

    private function describe(string $role): array
    {
        $rRole = $this->roleResolver->resolveRole($role);
        $mapper = $this->mapperProvider->getMapper($rRole);

        return [
            $rRole,
            $this->relationProvider->getRelationMap($rRole),
            $mapper,
            $this->schema->define($role, SchemaInterface::PRIMARY_KEY),
            $this->indexProvider->getIndexes($rRole),
            // Only the stock proxy mapper knows how to resolve deferred references.
            $mapper::class === Mapper::class,
        ];
    }
}

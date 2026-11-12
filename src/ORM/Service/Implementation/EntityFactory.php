<?php

declare(strict_types=1);

namespace Arcano\ORM\Service\Implementation;

use Arcano\ORM\Exception\MapperException;
use Arcano\ORM\Heap\Heap;
use Arcano\ORM\Heap\HeapInterface;
use Arcano\ORM\Heap\Node;
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
     * @param list<non-empty-string> $columns
     *
     * @return null|\Closure(iterable<array>): list<object>
     */
    public function rowMaker(string $role, array $columns): ?\Closure
    {
        return ($this->rowMakers[$role . "\0" . \implode("\0", $columns)] ??= $this->compileRowMaker($role, $columns) ?? false)
            ?: null;
    }

    /**
     * What the compiled makers need for a role and field list, or null when the role
     * needs the generic path (custom mapper or typecast, inheritance, composite key).
     *
     * @return array{string, \Arcano\ORM\RelationMap, string, list<string>, array, \Closure}|null
     */
    private function makerPlan(string $role, array $fields): ?array
    {
        [$rRole, $relMap, $mapper, $pk, $indexes, $deferReferences] = $this->roles[$role] ??= $this->describe($role);
        if (
            !$deferReferences
            || !$this->heap instanceof Heap
            || !\is_string($pk)
            || $rRole !== $role
            || $this->schema->define($role, SchemaInterface::PARENT) !== null
            || ($this->schema->define($role, SchemaInterface::CHILDREN) ?? []) !== []
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
        $hydrate = $mapper->compileRowHydrator($fields);

        return $hydrate === null ? null : [$rRole, $relMap, $pk, $keys, $rules, $hydrate];
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
        [$rRole, $relMap, $pk, $keys, $rules, $hydrate] = $plan;

        $items = [];
        foreach (\array_intersect_key($rules, \array_flip($fields)) as $field => $rule) {
            $key = \var_export($field, true);
            $items[] = "\$d[{$key}] === null or \$d[{$key}] = ({$rule}) \$d[{$key}];";
        }
        $cast = eval("declare(strict_types=1);\nreturn static function (array \$d): array {\n" . \implode("\n", $items) . "\nreturn \$d;\n};");
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

    private function compileRowMaker(string $role, array $columns): ?\Closure
    {
        $plan = $this->makerPlan($role, $columns);
        if ($plan === null) {
            return null;
        }
        [$rRole, $relMap, $pk, $keys, $rules, $hydrate] = $plan;
        $heap = $this->heap;
        $pkIndex = \array_search($pk, $columns, true);

        // Typecast compiled into the array literal: null stays null, as in Typecast::cast().
        $items = [];
        foreach ($columns as $i => $field) {
            $key = \var_export($field, true);
            $items[] = isset($rules[$field])
                ? "{$key} => \$r[{$i}] === null ? null : ({$rules[$field]}) \$r[{$i}]"
                : "{$key} => \$r[{$i}]";
        }
        $build = eval("declare(strict_types=1);\nreturn static fn (array \$r): array => [" . \implode(', ', $items) . '];');
        $deferRelations = $relMap->getRelations() !== [];

        // $fresh collects the ids of the entities created here (not taken from the heap),
        // $nodes the heap node of each result.
        return function (iterable $rows, ?array &$fresh = null, ?array &$nodes = null) use (
            $role, $rRole, $relMap, $pk, $pkIndex, $columns, $keys, $heap, $build, $hydrate, $deferRelations,
        ): array {
            $result = [];
            $seen = [];
            try {
                foreach ($rows as $row) {
                    $id = $row[$pkIndex];
                    if ($id === null) {
                        $result[] = $entity = $this->make($role, \array_combine($columns, $row), Node::MANAGED, typecast: true);
                        $nodes === null or $nodes[] = $heap->get($entity);
                        continue;
                    }
                    $id = (string) $id;
                    // Same as the parser: one entity per primary key.
                    if (isset($seen[$id])) {
                        continue;
                    }
                    $seen[$id] = true;
                    if ($heap->findKey($rRole, $pk, $id) !== null) {
                        $result[] = $entity = $this->make($role, \array_combine($columns, $row), Node::MANAGED, typecast: true);
                        $nodes === null or $nodes[] = $heap->get($entity);
                        continue;
                    }

                    $data = $build($row);
                    $node = new Node(Node::MANAGED, $data, $rRole, true);
                    $entity = $hydrate($data, $node);
                    $heap->attachLoaded($entity, $node, $rRole, $data, $keys);
                    $deferRelations and $node->deferRelations($relMap);
                    $fresh === null or $fresh[\spl_object_id($entity)] = true;
                    $nodes === null or $nodes[] = $node;
                    $result[] = $entity;
                }
            } catch (\TypeError $e) {
                throw new MapperException(
                    "Can't hydrate an entity because property and value types are incompatible.",
                    previous: $e,
                );
            }

            return $result;
        };
    }

    /**
     * Sets a loaded relation like {@see RelationMap::init()} does, on a new entity or
     * on a heap entity whose relation is still unresolved.
     *
     * @internal
     */
    public function attachLoadedRelation(string $role, object $entity, string $name, array $elements, bool $fresh): void
    {
        [, $relMap, $mapper] = $this->roles[$role] ??= $this->describe($role);
        if (!$fresh) {
            $fetched = $mapper->fetchRelations($entity);
            if (\array_key_exists($name, $fetched) && !$fetched[$name] instanceof ReferenceInterface) {
                return;
            }
        }
        $relation = $relMap->getRelations()[$name];
        $this->heap->get($entity)?->setRelation($name, $elements);
        $mapper->hydrate($entity, [$name => $relation->collect($elements)]);
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

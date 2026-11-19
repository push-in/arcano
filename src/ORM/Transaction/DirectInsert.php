<?php

declare(strict_types=1);

namespace Arcano\ORM\Transaction;

use Arcano\ORM\Command\Database\StatementPlans;
use Doctrine\Common\Collections\Collection;
use Arcano\ORM\Exception\TransactionException;
use Arcano\ORM\Heap\Heap;
use Arcano\ORM\Heap\Node;
use Arcano\ORM\Mapper\Mapper;
use Arcano\ORM\ORMInterface;
use Arcano\ORM\Reference\ReferenceInterface;
use Arcano\ORM\Relation\HasMany;
use Arcano\ORM\Relation\ShadowBelongsTo;
use Arcano\ORM\Schema\GeneratedField;
use Arcano\ORM\SchemaInterface;
use Arcano\ORM\Service\IndexProviderInterface;

/**
 * Inserts new entities and their new has-many children without the pool, with the
 * statements, order and final heap state of the generic unit of work.
 *
 * @internal
 */
final class DirectInsert
{
    /** @var array<class-string, string|false> */
    private array $classRoles = [];

    /** @var array<string, array|false> */
    private array $roles = [];

    public function __construct(
        private ORMInterface $orm,
        private Heap $heap,
    ) {}

    /**
     * The insert of a new entity graph, or null when it needs the pool.
     *
     * @param array<int, mixed> $persisted entities stored on their own, by object id
     * @param array<int, true> $claimed entities already planned, by object id
     */
    public function plan(object $entity, bool $cascade, array $persisted, array &$claimed): ?array
    {
        $root = $this->describeEntity($entity, $claimed);
        if ($root === null || $root['masters'] !== []) {
            return null;
        }
        $fields = $root['mapper']->fetchFields($entity);
        if ($fields === []) {
            // Nothing to write: the command generator decides between an empty insert and no write.
            return null;
        }

        $slaves = [];
        if ($cascade && $root['slaves'] !== []) {
            $relations = $root['mapper']->fetchRelations($entity);
            foreach ($root['slaves'] as $name => [$innerKeys, $outerKeys, $childRelation, $target, $isCascade]) {
                if (!$isCascade) {
                    continue;
                }
                $value = $relations[$name] ?? null;
                if ($value instanceof Collection) {
                    $items = $value->toArray();
                } elseif (\is_array($value)) {
                    $items = $value;
                } else {
                    return null;
                }
                foreach ($innerKeys as $key) {
                    if ($key !== $root['pk'] && !isset($fields[$key])) {
                        return null;
                    }
                }
                $children = [];
                foreach ($items as $child) {
                    if (!\is_object($child) || isset($persisted[\spl_object_id($child)])) {
                        return null;
                    }
                    $describe = $this->describeEntity($child, $claimed);
                    if (
                        $describe === null
                        || $describe['role'] !== $target
                        || $describe['slaves'] !== []
                        || $describe['masters'] !== [$childRelation]
                    ) {
                        return null;
                    }
                    foreach ($outerKeys as $key) {
                        if (!isset($describe['columns'][$key])) {
                            return null;
                        }
                    }
                    $children[] = [$child, $describe, $describe['mapper']->fetchFields($child)];
                }
                $slaves[$name] = [$value, $children, $innerKeys, $outerKeys, $childRelation];
            }
        }

        return [$entity, $root, $fields, $slaves];
    }

    /**
     * Runs the inserts of a planned graph and returns the data to sync after commit.
     *
     * @param \Closure(\Arcano\Database\DatabaseInterface): void $begin opens the transaction of a database
     */
    public function execute(array $op, \Closure $begin): array
    {
        [$entity, $root, $fields, $slaves] = $op;
        $data = $this->insert($root, $fields, $begin);

        $rows = [];
        foreach ($slaves as $name => [, $children, $innerKeys, $outerKeys]) {
            foreach ($children as $i => [, $describe, $childFields]) {
                foreach ($innerKeys as $k => $key) {
                    $childFields[$outerKeys[$k]] = $data[$key] ?? throw new TransactionException(
                        "Can't store `{$root['role']}.{$name}`: the `{$key}` key was not generated.",
                    );
                }
                $rows[$name][$i] = $this->insert($describe, $childFields, $begin);
            }
        }

        return [$data, $rows];
    }

    /**
     * Creates the managed nodes and hydrates the generated values, as {@see UnitOfWork::syncHeap()}.
     */
    public function sync(array $op, array $result): void
    {
        [$entity, $root, $fields, $slaves] = $op;
        [$data, $rows] = $result;

        $node = new Node(Node::MANAGED, $data, $root['role']);
        foreach ($slaves as $name => [$value]) {
            $node->setRelation($name, $value);
        }
        $this->attach($entity, $node, $root, $fields, $data);

        foreach ($slaves as $name => [, $children, , , $childRelation]) {
            foreach ($children as $i => [$child, $describe, $childFields]) {
                $childData = $rows[$name][$i];
                $childNode = new Node(Node::MANAGED, $childData, $describe['role']);
                $childNode->setRelation($childRelation, $entity);
                $this->attach($child, $childNode, $describe, $childFields, $childData);
            }
        }
    }

    private function attach(object $entity, Node $node, array $describe, array $fields, array $data): void
    {
        $this->heap->attach($entity, $node, $describe['indexes']);
        $changes = [];
        foreach ($data as $field => $value) {
            if (!\array_key_exists($field, $fields) || $fields[$field] !== $value) {
                $changes[$field] = $value;
            }
        }
        $changes === [] or $describe['mapper']->hydrate($entity, $changes);
    }

    /**
     * {@see \Arcano\ORM\Command\Database\Insert::execute()} over plain data.
     */
    private function insert(array $describe, array $data, \Closure $begin): array
    {
        /** @var Mapper $mapper */
        $mapper = $describe['mapper'];
        [$primaryKeys, $pkColumn, $generated] = $describe['keys'];
        $source = $describe['source'];
        $db = $source->getDatabase();
        $table = $source->getTable();
        $begin($db);

        $uncasted = $data;
        foreach ($primaryKeys as $key) {
            if (!isset($uncasted[$key])) {
                unset($uncasted[$key]);
            }
        }
        $returningFields = [];
        foreach ($generated as $column => $mode) {
            if (($mode & GeneratedField::ON_INSERT) === 0x0) {
                continue;
            }
            $returningFields[$column] = $mode;
            if (!isset($uncasted[$column])) {
                unset($uncasted[$column]);
            }
        }
        $uncasted = $mapper->uncast($uncasted);
        $values = $mapper->mapColumns($uncasted);

        if ($pkColumn !== null && $returningFields === []) {
            $returningFields[$primaryKeys[0]] ??= $pkColumn;
        }
        $returning = [];
        if ($returningFields !== [] && StatementPlans::returns($db, $table)) {
            $columns = $mapper->mapColumns($returningFields);
            $returning = \array_combine(\array_keys($returningFields), \array_keys($columns));
        }

        [$done, $insertID] = StatementPlans::insert($db, $table, $values, \array_values($returning));
        if (!$done) {
            $query = $db->insert($table)->values($values);
            $returning === [] or $query->returning(...\array_values($returning));
            $insertID = $query->run();
        }

        if ($returning !== []) {
            if (\count($returning) === 1) {
                $field = \array_key_first($returning);
                $data[$field] = $mapper->cast([$field => $insertID])[$field];
            } else {
                foreach ($mapper->cast($insertID) as $field => $value) {
                    $data[$field] = $value;
                }
            }
        } elseif ($insertID !== null && \count($primaryKeys) === 1 && !isset($data[$primaryKeys[0]])) {
            $data[$primaryKeys[0]] = $mapper->cast([$primaryKeys[0] => $insertID])[$primaryKeys[0]];
        }

        return $data;
    }

    /**
     * Role description of a new entity that is not planned yet, or null.
     */
    private function describeEntity(object $entity, array &$claimed): ?array
    {
        $id = \spl_object_id($entity);
        if ($entity instanceof ReferenceInterface || isset($claimed[$id]) || $this->heap->get($entity) !== null) {
            return null;
        }
        $claimed[$id] = true;

        $role = $this->classRoles[$entity::class] ??= $this->resolveRole($entity);
        if ($role === false) {
            return null;
        }
        $describe = $this->roles[$role] ??= $this->describe($role) ?? false;

        return $describe === false ? null : $describe;
    }

    private function resolveRole(object $entity): string|false
    {
        try {
            return $this->orm->resolveRole($entity);
        } catch (\Throwable) {
            return false;
        }
    }

    private function describe(string $role): ?array
    {
        $schema = $this->orm->getSchema();
        $mapper = $this->orm->getMapper($role);
        $pk = $schema->define($role, SchemaInterface::PRIMARY_KEY);
        $map = $this->orm->getRelationMap($role);
        if (
            $mapper::class !== Mapper::class
            || !\is_string($pk)
            || $schema->define($role, SchemaInterface::PARENT) !== null
            || ($schema->define($role, SchemaInterface::CHILDREN) ?? []) !== []
            || $map->hasEmbedded()
            || \count($map->getSlaves()) !== \count($map->getRelations())
        ) {
            return null;
        }

        $masters = [];
        foreach ($map->getMasters() as $name => $relation) {
            if (!$relation instanceof ShadowBelongsTo) {
                return null;
            }
            $masters[] = $name;
        }
        $slaves = [];
        foreach ($map->getSlaves() as $name => $relation) {
            $keys = $relation::class === HasMany::class ? $relation->directInsertKeys() : null;
            if ($keys === null) {
                return null;
            }
            $slaves[$name] = [...$keys, $relation->getTarget(), $relation->isCascade()];
        }

        $keys = $mapper->insertKeys();
        foreach ($keys[2] as $field => $mode) {
            // Fields generated before insert need the command generator.
            if (($mode & GeneratedField::BEFORE_INSERT) !== 0x0) {
                return null;
            }
        }

        $columns = $schema->define($role, SchemaInterface::COLUMNS) ?? [];
        $fields = [];
        foreach ($columns as $field => $column) {
            $fields[\is_int($field) ? $column : $field] = true;
        }

        return [
            'role' => $role,
            'mapper' => $mapper,
            'pk' => $pk,
            'keys' => $keys,
            'columns' => $fields,
            'indexes' => $this->orm->getService(IndexProviderInterface::class)->getIndexes($role),
            'source' => $this->orm->getSource($role),
            'masters' => $masters,
            'slaves' => $slaves,
        ];
    }
}

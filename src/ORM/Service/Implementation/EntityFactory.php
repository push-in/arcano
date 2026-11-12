<?php

declare(strict_types=1);

namespace Arcano\ORM\Service\Implementation;

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

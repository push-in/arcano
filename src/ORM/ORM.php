<?php

declare(strict_types=1);

namespace Arcano\ORM;

use Arcano\ORM\Heap\Heap;
use Arcano\ORM\Heap\HeapInterface;
use Arcano\ORM\Heap\Node;
use Arcano\ORM\Select\SourceInterface;
use Arcano\ORM\Service\EntityFactoryInterface;
use Arcano\ORM\Service\EntityProviderInterface;
use Arcano\ORM\Service\Implementation\EntityFactory;
use Arcano\ORM\Service\Implementation\EntityProvider;
use Arcano\ORM\Service\Implementation\IndexProvider;
use Arcano\ORM\Service\Implementation\MapperProvider;
use Arcano\ORM\Service\Implementation\RelationProvider;
use Arcano\ORM\Service\Implementation\RepositoryProvider;
use Arcano\ORM\Service\Implementation\RoleResolver;
use Arcano\ORM\Service\Implementation\SourceProvider;
use Arcano\ORM\Service\Implementation\TypecastProvider;
use Arcano\ORM\Service\IndexProviderInterface;
use Arcano\ORM\Service\MapperProviderInterface;
use Arcano\ORM\Service\RelationProviderInterface;
use Arcano\ORM\Service\RepositoryProviderInterface;
use Arcano\ORM\Service\RoleResolverInterface;
use Arcano\ORM\Service\SourceProviderInterface;
use Arcano\ORM\Service\TypecastProviderInterface;
use Arcano\ORM\Transaction\CommandGenerator;
use Arcano\ORM\Transaction\CommandGeneratorInterface;
use JetBrains\PhpStorm\ExpectedValues;

/**
 * Central class ORM, provides access to various pieces of the system and manages schema state.
 */
final class ORM implements ORMInterface
{
    private HeapInterface $heap;
    private CommandGeneratorInterface $commandGenerator;
    private RelationProvider $relationProvider;
    private SourceProvider $sourceProvider;
    private TypecastProvider $typecastProvider;
    private EntityFactoryInterface $entityFactory;
    private IndexProvider $indexProvider;
    private MapperProvider $mapperProvider;
    private RepositoryProvider $repositoryProvider;
    private EntityProvider $entityProvider;
    private RoleResolverInterface $roleResolver;
    private Options $options;

    public function __construct(
        private FactoryInterface $factory,
        private SchemaInterface $schema,
        ?CommandGeneratorInterface $commandGenerator = null,
        ?HeapInterface $heap = null,
        ?Options $options = null,
    ) {
        $this->heap = $heap ?? new Heap();
        $this->commandGenerator = $commandGenerator ?? new CommandGenerator();
        $this->resetRegistry();
        $this->options = $options ?? new Options();
    }

    public function resolveRole(string|object $entity): string
    {
        return $this->roleResolver->resolveRole($entity);
    }

    public function get(string $role, array $scope, bool $load = true): ?object
    {
        $role = $this->resolveRole($role);
        return $this->entityProvider->get($role, $scope, $load);
    }

    public function make(string $role, array $data = [], int $status = Node::NEW, bool $typecast = false): object
    {
        return $this->entityFactory->make($role, $data, $status, $typecast);
    }

    public function getCommandGenerator(): CommandGeneratorInterface
    {
        return $this->commandGenerator;
    }

    public function getFactory(): FactoryInterface
    {
        return $this->factory;
    }

    public function getService(
        #[ExpectedValues(values: [
            EntityFactoryInterface::class,
            IndexProviderInterface::class,
            MapperProviderInterface::class,
            RelationProviderInterface::class,
            RepositoryProviderInterface::class,
            SourceProviderInterface::class,
            TypecastProviderInterface::class,
            Options::class,
        ])]
        string $class,
    ): object {
        return match ($class) {
            EntityFactoryInterface::class => $this->entityFactory,
            EntityProviderInterface::class => $this->entityProvider,
            SourceProviderInterface::class => $this->sourceProvider,
            TypecastProviderInterface::class => $this->typecastProvider,
            IndexProviderInterface::class => $this->indexProvider,
            MapperProviderInterface::class => $this->mapperProvider,
            RelationProviderInterface::class => $this->relationProvider,
            RepositoryProviderInterface::class => $this->repositoryProvider,
            Options::class => $this->options,
            default => throw new \InvalidArgumentException("Undefined service `$class`."),
        };
    }

    public function getSchema(): SchemaInterface
    {
        return $this->schema;
    }

    public function getHeap(): HeapInterface
    {
        return $this->heap;
    }

    public function getMapper(string|object $entity): MapperInterface
    {
        return $this->mapperProvider->getMapper(
            $this->resolveRole($entity),
        );
    }

    public function getRepository(string|object $entity): RepositoryInterface
    {
        return $this->repositoryProvider->getRepository(
            $this->resolveRole($entity),
        );
    }

    public function getSource(string $entity): SourceInterface
    {
        return $this->sourceProvider->getSource(
            $this->resolveRole($entity),
        );
    }

    public function getIndexes(string $entity): array
    {
        return $this->indexProvider->getIndexes(
            $this->resolveRole($entity),
        );
    }

    /**
     * Get relation map associated with the given role or class.
     */
    public function getRelationMap(string $entity): RelationMap
    {
        return $this->relationProvider->getRelationMap(
            $this->resolveRole($entity),
        );
    }

    public function with(
        ?SchemaInterface $schema = null,
        ?FactoryInterface $factory = null,
        ?HeapInterface $heap = null,
        ?Options $options = null,
    ): ORMInterface {
        $heap ??= clone $this->heap;
        $heap->clean();

        return new self(
            factory: $factory ?? $this->factory,
            schema: $schema ?? $this->schema,
            commandGenerator: $this->commandGenerator,
            heap: $heap,
            options: $options ?? $this->options,
        );
    }

    /**
     * @deprecated since Cycle ORM v1.8, this method will be removed in future releases.
     * Use method {@see with} instead.
     */
    public function withSchema(SchemaInterface $schema): ORMInterface
    {
        return $this->with($schema);
    }

    /**
     * @deprecated since Cycle ORM v1.8, this method will be removed in future releases.
     * Use method {@see with} instead.
     */
    public function withFactory(FactoryInterface $factory): ORMInterface
    {
        return $this->with(factory: $factory);
    }

    /**
     * Warmup ORM and preload all internal services.
     */
    public function prepareServices(): void
    {
        // Preload providers with back to ORM reference
        $this->relationProvider->prepareRelationMaps();
        $this->repositoryProvider->prepareRepositories();
        $this->mapperProvider->prepareMappers();
    }

    /**
     * @deprecated since Cycle ORM v1.8, this method will be removed in future releases.
     * Use method {@see with} instead.
     */
    public function withHeap(HeapInterface $heap): ORMInterface
    {
        return $this->with(heap: $heap);
    }

    /**
     * Reset related objects cache.
     */
    public function __clone()
    {
        $this->heap = clone $this->heap;
        $this->heap->clean();

        $this->resetRegistry();
    }

    public function __debugInfo(): array
    {
        return [
            'schema' => $this->schema,
        ];
    }

    private function resetRegistry(): void
    {
        $this->indexProvider = new IndexProvider($this->schema);
        $this->sourceProvider = new SourceProvider($this->factory, $this->schema);
        $this->typecastProvider = new TypecastProvider($this->factory, $this->schema, $this->sourceProvider);

        // With back to ORM reference
        $this->relationProvider = new RelationProvider($this);
        $this->mapperProvider = new MapperProvider($this, $this->factory);
        $this->repositoryProvider = new RepositoryProvider(
            $this,
            $this->sourceProvider,
            $this->schema,
            $this->factory,
        );
        $this->entityProvider = new EntityProvider($this->heap, $this->repositoryProvider);
        $this->roleResolver = new RoleResolver($this->schema, $this->heap);

        $this->entityFactory = new EntityFactory(
            $this->heap,
            $this->schema,
            $this->mapperProvider,
            $this->relationProvider,
            $this->indexProvider,
            $this->roleResolver,
        );
    }
}

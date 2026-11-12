<?php

declare(strict_types=1);

namespace Arcano\ORM;

use Arcano\Database\DatabaseInterface;
use Arcano\Database\DatabaseProviderInterface;
use Arcano\ORM\Collection\CollectionFactoryInterface;
use Arcano\ORM\Parser\TypecastInterface;
use Arcano\ORM\Service\SourceProviderInterface;
use Arcano\ORM\Relation\RelationInterface;
use Arcano\ORM\Select\LoaderInterface;
use Arcano\ORM\Select\SourceInterface;
use Spiral\Core\FactoryInterface as CoreFactory;

/**
 * Must provide access to generic DI.
 */
interface FactoryInterface extends DatabaseProviderInterface, CoreFactory
{
    public const PARENT_LOADER = '::parent::';
    public const CHILD_LOADER = '::child::';

    /**
     * Create mapper associated with given role.
     */
    public function mapper(
        ORMInterface $orm,
        string $role,
    ): MapperInterface;

    /**
     * Create loader associated with specific entity and relation.
     */
    public function loader(
        SchemaInterface $schema,
        SourceProviderInterface $sourceProvider,
        string $role,
        string $relation,
    ): LoaderInterface;

    /**
     * @template TEntity of object
     *
     * Create repository associated with given role.
     *
     * @param non-empty-string|class-string<TEntity> $role
     * @return ($role is class-string<TEntity> ? RepositoryInterface<TEntity> : RepositoryInterface<object>)
     */
    public function repository(
        ORMInterface $orm,
        SchemaInterface $schema,
        string $role,
        ?Select $select,
    ): RepositoryInterface;

    /**
     * Create typecast implementation associated with given role.
     */
    public function typecast(
        SchemaInterface $schema,
        DatabaseInterface $database,
        string $role,
    ): ?TypecastInterface;

    /**
     * Create source associated with given role.
     */
    public function source(
        SchemaInterface $schema,
        string $role,
    ): SourceInterface;

    /**
     * @param class-string|string|null $name Collection factory name.
     *        Can be class name or alias that can be configured in the {@see withCollectionFactory()} method.
     */
    public function collection(
        ?string $name = null,
    ): CollectionFactoryInterface;

    /**
     * Create relation associated with specific entity and relation.
     */
    public function relation(
        ORMInterface $orm,
        SchemaInterface $schema,
        string $role,
        string $relation,
    ): RelationInterface;

    /**
     * Add default classes for producing
     */
    public function withDefaultSchemaClasses(array $defaults): self;

    /**
     * Configure additional collection factories.
     *
     * @param string $alias Collection alias name that can be used in {@see Relation::COLLECTION_TYPE} parameter.
     * @param class-string|null $interface An interface or base class that is common to the collections produced.
     */
    public function withCollectionFactory(
        string $alias,
        CollectionFactoryInterface $factory,
        ?string $interface = null,
    ): self;
}

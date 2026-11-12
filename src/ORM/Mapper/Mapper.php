<?php

declare(strict_types=1);

namespace Arcano\ORM\Mapper;

use Arcano\ORM\Heap\Node;
use Arcano\ORM\Mapper\Traits\SingleTableTrait;
use Arcano\ORM\ORMInterface;
use Arcano\ORM\Mapper\Proxy\ProxyEntityFactory;
use Arcano\ORM\SchemaInterface;

/**
 * Provide the ability to carry data over the specific class instances using proxy classes.
 *
 * Supports table inheritance using hidden entity field.
 */
class Mapper extends DatabaseMapper
{
    use SingleTableTrait;

    /** @var class-string */
    protected string $entity;

    protected array $children = [];

    public function __construct(
        ORMInterface $orm,
        protected ProxyEntityFactory $entityFactory,
        string $role,
    ) {
        parent::__construct($orm, $role);

        $this->schema = $orm->getSchema();
        $this->entity = $this->schema->define($role, SchemaInterface::ENTITY);
        $this->children = $this->schema->define($role, SchemaInterface::CHILDREN) ?? [];
        $this->discriminator = $this->schema->define($role, SchemaInterface::DISCRIMINATOR) ?? $this->discriminator;
    }

    public function init(array $data, ?string $role = null): object
    {
        $class = $this->resolveClass($data, $role);
        return $this->entityFactory->create($this->relationMap, $class);
    }

    /**
     * A closure that builds a managed entity straight from loaded field data,
     * or null when the class needs the generic path.
     *
     * @internal
     *
     * @param list<non-empty-string> $fields
     *
     * @return null|\Closure(array, Node): object
     */
    public function compileRowHydrator(array $fields): ?\Closure
    {
        return $this->children === []
            ? $this->entityFactory->compileRowHydrator($this->relationMap, $this->entity, $fields)
            : null;
    }

    /**
     * @internal
     *
     * @see ProxyEntityFactory::rowPlan()
     */
    public function rowPlan(array $fields): ?array
    {
        return $this->children === []
            ? $this->entityFactory->rowPlan($this->relationMap, $this->entity, $fields)
            : null;
    }

    public function hydrate(object $entity, array $data): object
    {
        $this->entityFactory->upgrade($this->relationMap, $entity, $data);
        return $entity;
    }

    public function extract(object $entity): array
    {
        return $this->entityFactory->extractData($this->relationMap, $entity)
            + $this->entityFactory->extractRelations($this->relationMap, $entity);
    }

    public function fetchFields(object $entity): array
    {
        $values = \array_intersect_key(
            $this->entityFactory->extractData($this->relationMap, $entity),
            $this->columns + $this->parentColumns,
        );
        return $values + $this->getDiscriminatorValues($entity);
    }

    public function fetchRelations(object $entity): array
    {
        return $this->entityFactory->extractRelations($this->relationMap, $entity);
    }
}

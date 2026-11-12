<?php

declare(strict_types=1);

namespace Arcano\ORM\Select;

use Arcano\ORM\RepositoryInterface;
use Arcano\ORM\Select;

/**
 * Repository provides ability to load entities and construct queries.
 *
 * @template TEntity of object
 *
 * @implements RepositoryInterface<TEntity>
 */
class Repository implements RepositoryInterface
{
    /**
     * Create repository linked to one specific selector.
     *
     * @param Select<TEntity> $select
     */
    /** Compiled findByPK() statement; false when the select can't use one. */
    private array|false|null $primaryKeyPlan = null;

    public function __construct(
        /** @readonly */
        protected Select $select,
    ) {}

    public function findByPK($id): ?object
    {
        if (\is_int($id) || \is_string($id)) {
            $plan = $this->primaryKeyPlan ??= $this->select->primaryKeyPlan() ?? false;
            if ($plan !== false) {
                return $this->select->fetchByPrimaryKeyPlan($plan, $id);
            }
        }

        return $this->select()->wherePK($id)->fetchOne();
    }

    public function findOne(array $scope = []): ?object
    {
        return $this->select()->fetchOne($scope);
    }

    public function findAll(array $scope = [], array $orderBy = []): iterable
    {
        return $this->select()->where($scope)->orderBy($orderBy)->fetchAll();
    }

    /**
     * Get selector associated with the repository.
     *
     * @return Select<TEntity>
     */
    public function select(): Select
    {
        // Clones share the loader of this select until they use it.
        $this->select->share();
        return clone $this->select;
    }

    public function forUpdate(): static
    {
        $repository = clone $this;
        $repository->select->forUpdate();
        $repository->primaryKeyPlan = false;

        return $repository;
    }

    /**
     * Repositories are always immutable by default.
     */
    public function __clone()
    {
        $this->select = clone $this->select;
        $this->primaryKeyPlan = null;
    }
}

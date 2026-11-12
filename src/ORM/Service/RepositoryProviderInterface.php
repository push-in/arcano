<?php

declare(strict_types=1);

namespace Arcano\ORM\Service;

use Arcano\ORM\RepositoryInterface;

interface RepositoryProviderInterface
{
    /**
     * @template TEntity of object
     *
     * Get repository associated with given entity role.
     *
     * @param class-string<TEntity>|non-empty-string $entity
     * @return ($entity is class-string<TEntity> ? RepositoryInterface<TEntity> : RepositoryInterface<object>)
     */
    public function getRepository(string $entity): RepositoryInterface;
}

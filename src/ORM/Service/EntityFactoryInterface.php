<?php

declare(strict_types=1);

namespace Arcano\ORM\Service;

use Arcano\ORM\Exception\MapperException;
use Arcano\ORM\Heap\Node;

interface EntityFactoryInterface
{
    /**
     * Create new entity based on given role and input data.
     *
     * @template T
     *
     * @param non-empty-string|class-string<T> $role Entity role.
     * @param array<non-empty-string, mixed> $data Entity data.
     * @param bool $typecast Indicates that data is raw, and typecasting should be applied.
     *
     * @return T
     * @throws MapperException
     */
    public function make(string $role, array $data = [], int $status = Node::NEW, bool $typecast = false): object;
}

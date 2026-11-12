<?php

declare(strict_types=1);

namespace Arcano\ORM\Service;

interface RoleResolverInterface
{
    /**
     * Automatically resolve role based on object name or instance.
     *
     * @return non-empty-string
     */
    public function resolveRole(string|object $entity): string;
}

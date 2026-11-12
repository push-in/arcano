<?php

declare(strict_types=1);

namespace Arcano\ORM\Service;

use Arcano\ORM\Parser\TypecastInterface;

interface TypecastProviderInterface
{
    /**
     * Get typecast implementation associated with given entity role.
     *
     * @param non-empty-string $role
     */
    public function getTypecast(string $role): ?TypecastInterface;
}

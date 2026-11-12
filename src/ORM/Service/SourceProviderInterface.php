<?php

declare(strict_types=1);

namespace Arcano\ORM\Service;

use Arcano\ORM\Select\SourceInterface;

interface SourceProviderInterface
{
    /**
     * Get database source associated with given entity role.
     */
    public function getSource(string $entity): SourceInterface;
}

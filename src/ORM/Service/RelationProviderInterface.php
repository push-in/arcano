<?php

declare(strict_types=1);

namespace Arcano\ORM\Service;

use Arcano\ORM\RelationMap;

interface RelationProviderInterface
{
    /**
     * Get relation map associated with given entity role.
     */
    public function getRelationMap(string $entity): RelationMap;
}

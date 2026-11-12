<?php

declare(strict_types=1);

namespace Arcano\Schema\Generator;

use Arcano\Schema\Definition\Entity;
use Arcano\Schema\GeneratorInterface;
use Arcano\Schema\Registry;

/**
 * Renders all required relations columns, indexes and foreign keys.
 */
final class RenderRelations implements GeneratorInterface
{
    public function run(Registry $registry): Registry
    {
        foreach ($registry as $entity) {
            $this->compute($registry, $entity);
        }

        return $registry;
    }

    protected function compute(Registry $registry, Entity $entity): void
    {
        foreach ($registry->getRelations($entity) as $relation) {
            $relation->render($registry);
        }
    }
}

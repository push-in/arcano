<?php

declare(strict_types=1);

namespace Arcano\Schema\Generator;

use Arcano\Schema\Definition\Entity;
use Arcano\Schema\Exception\SchemaException;
use Arcano\Schema\Exception\SchemaModifierException;
use Arcano\Schema\GeneratorInterface;
use Arcano\Schema\Registry;
use Arcano\Schema\SchemaModifierInterface;

/**
 * Generate modifiers based on their schematic definitions.
 */
final class GenerateModifiers implements GeneratorInterface
{
    public function run(Registry $registry): Registry
    {
        foreach ($registry as $entity) {
            $this->register($registry, $entity);
        }

        return $registry;
    }

    protected function register(Registry $registry, Entity $entity): void
    {
        $role = $entity->getRole();
        assert($role !== null);
        foreach ($entity->getSchemaModifiers() as $modifier) {
            \assert($modifier instanceof SchemaModifierInterface);
            try {
                $modifier->compute($registry);
            } catch (SchemaModifierException $e) {
                throw new SchemaException(
                    sprintf('Unable to compute modifier `%s` for the `%s` role.', $modifier::class, $role),
                    $e->getCode(),
                    $e,
                );
            }
        }
    }
}

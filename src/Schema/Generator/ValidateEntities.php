<?php

declare(strict_types=1);

namespace Arcano\Schema\Generator;

use Arcano\Schema\Exception\EntityException;
use Arcano\Schema\GeneratorInterface;
use Arcano\Schema\Registry;

final class ValidateEntities implements GeneratorInterface
{
    /**
     *
     * @throws EntityException
     *
     */
    public function run(Registry $registry): Registry
    {
        foreach ($registry->getIterator() as $entity) {
            if (count($entity->getFields()) === 0) {
                throw new EntityException(
                    "Entity `{$entity->getRole()}` is empty",
                );
            }
        }

        return $registry;
    }
}

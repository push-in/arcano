<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Arcano\Schema\Tests\Fixtures;

use Arcano\Schema\Definition\Entity;
use Arcano\Schema\Definition\Field;

class EmbeddedEntity
{
    public static function define(): Entity
    {
        $entity = new Entity();
        $entity->setRole('embedded');
        $entity->setClass(self::class);

        $entity->getFields()->set(
            'p_embedded',
            (new Field())->setType('string')->setColumn('embedded_column'),
        );

        return $entity;
    }
}

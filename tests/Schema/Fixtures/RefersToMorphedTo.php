<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Fixtures;

use Arcano\Schema\Definition\Entity;
use Arcano\Schema\Definition\Field;
use Arcano\Schema\Definition\Relation;

class RefersToMorphedTo
{
    public static function define(): Entity
    {
        $entity = new Entity();
        $entity->setRole('morphed');
        $entity->setClass(self::class);

        $entity->getFields()->set(
            'p_id',
            (new Field())->setType('primary')->setColumn('id')->setPrimary(true),
        );

        $entity->getRelations()->set(
            'parent',
            (new Relation())->setTarget(ParentInterface::class)->setType('refersToMorphed'),
        );

        return $entity;
    }
}

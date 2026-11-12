<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Fixtures;

use Arcano\Schema\Definition\Entity;
use Arcano\Schema\Definition\Field;
use Arcano\Schema\Definition\Relation;

class Post implements ParentInterface
{
    public static function define(): Entity
    {
        $entity = new Entity();
        $entity->setRole('post');
        $entity->setClass(self::class);

        $entity->getFields()->set(
            'p_id',
            (new Field())->setType('primary')->setColumn('id')->setPrimary(true),
        );

        $entity->getRelations()->set(
            'author',
            (new Relation())->setTarget(Author::class)->setType('belongsTo'),
        );

        return $entity;
    }

    public static function defineWithoutPK(): Entity
    {
        $entity = self::define();

        $entity->getFields()->remove('p_id');

        $entity->getFields()->set(
            'p_id',
            (new Field())->setColumn('id'),
        );

        return $entity;
    }

    public static function defineCompositePK(): Entity
    {
        $entity = self::define();

        $entity->getFields()->set(
            'p_slug',
            (new Field())->setType('string')->setColumn('slug')->setPrimary(true),
        );

        return $entity;
    }
}

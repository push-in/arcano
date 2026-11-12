<?php

declare(strict_types=1);

use Arcano\ORM\Mapper\Mapper;
use Arcano\ORM\Relation;
use Arcano\ORM\SchemaInterface as Schema;
use Arcano\ORM\Select\Repository;
use Arcano\ORM\Select\Source;
use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Issue422\Entity\Billing;
use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Issue422\Entity\User;
use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Issue422\Entity\SomeEmbedded;

return [
    'user' => [
        Schema::ENTITY => User::class,
        Schema::MAPPER => Mapper::class,
        Schema::SOURCE => Source::class,
        Schema::DATABASE => 'default',
        Schema::TABLE => 'user',
        Schema::PRIMARY_KEY => ['id'],
        Schema::FIND_BY_KEYS => ['id'],
        Schema::COLUMNS => [
            'id' => 'id',
        ],
        Schema::RELATIONS => [
            'billing' => [
                Relation::TYPE => Relation::HAS_ONE,
                Relation::TARGET => 'billing',
                Relation::LOAD => Relation::LOAD_PROMISE,
                Relation::SCHEMA => [
                    Relation::CASCADE => true,
                    Relation::NULLABLE => true,
                    Relation::INNER_KEY => ['id'],
                    Relation::OUTER_KEY => 'user_id',
                ],
            ],
            'otherEmbedded' => [
                Relation::TYPE => Relation::EMBEDDED,
                Relation::TARGET => 'user:otherEmbeddable:otherEmbeddable',
                Relation::LOAD => Relation::LOAD_EAGER,
                Relation::SCHEMA => [],
            ],
        ],
        Schema::SCOPE => null,
        Schema::TYPECAST => [
            'id' => 'int',
        ],
        Schema::SCHEMA => [],
    ],
    'billing' => [
        Schema::ENTITY => Billing::class,
        Schema::MAPPER => Arcano\ORM\Mapper\Mapper::class,
        Schema::SOURCE => Arcano\ORM\Select\Source::class,
        Schema::DATABASE => 'default',
        Schema::TABLE => 'billing',
        Schema::PRIMARY_KEY => ['id'],
        Schema::FIND_BY_KEYS => ['id'],
        Schema::COLUMNS => [
            'id' => 'id',
            'user_id' => 'user_id',
        ],
        Schema::RELATIONS => [
            'someEmbeddable' => [
                Relation::TYPE => Relation::EMBEDDED,
                Relation::TARGET => 'billing:someEmbeddable:someEmbeddable',
                Relation::LOAD => Relation::LOAD_EAGER,
                Relation::SCHEMA => [],
            ],
        ],
        Schema::SCOPE => null,
        Schema::TYPECAST => [
            'id' => 'int',
        ],
        Schema::SCHEMA => [],
    ],
    'billing:someEmbeddable:someEmbeddable' => [
        Schema::ENTITY => SomeEmbedded::class,
        Schema::MAPPER => Mapper::class,
        Schema::SOURCE => Source::class,
        Schema::REPOSITORY => Repository::class,
        Schema::DATABASE => 'default',
        Schema::TABLE => 'billing',
        Schema::PRIMARY_KEY => ['id'],
        Schema::FIND_BY_KEYS => ['id'],
        Schema::COLUMNS => [
            'propertyString' => 'property_string',
            'propertyInt' => 'property_int',
            'id' => 'id',
        ],
        Schema::RELATIONS => [],
        Schema::SCOPE => null,
        Schema::TYPECAST => [
            'propertyString' => 'string',
            'propertyInt' => 'int',
            'id' => 'int',
        ],
        Schema::SCHEMA => [],
    ],
    'user:otherEmbeddable:otherEmbeddable' => [
        Schema::ENTITY => SomeEmbedded::class,
        Schema::MAPPER => Mapper::class,
        Schema::SOURCE => Source::class,
        Schema::REPOSITORY => Repository::class,
        Schema::DATABASE => 'default',
        Schema::TABLE => 'user',
        Schema::PRIMARY_KEY => ['id'],
        Schema::FIND_BY_KEYS => ['id'],
        Schema::COLUMNS => [
            'propertyString' => 'property_string',
            'propertyInt' => 'property_int',
            'id' => 'id',
        ],
        Schema::RELATIONS => [],
        Schema::SCOPE => null,
        Schema::TYPECAST => [
            'propertyString' => 'string',
            'propertyInt' => 'int',
            'id' => 'int',
        ],
        Schema::SCHEMA => [],
    ],
];

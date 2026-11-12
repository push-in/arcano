<?php

declare(strict_types=1);

use Arcano\ORM\Parser\Typecast;
use Arcano\ORM\Relation;
use Arcano\ORM\SchemaInterface as Schema;
use Arcano\ORM\Select\Source;
use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Case408\Entity\PingMonitor;
use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Case408\Entity\Pivot;
use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Case408\Entity\Target;
use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Case408\Entity\TargetGroup;
use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Case408\Type;
use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Case408\Type\TypeCaster;

return [
    'target' => [
        Schema::ENTITY => Target::class,
        Schema::SOURCE => Source::class,
        Schema::DATABASE => 'default',
        Schema::TABLE => 'targets',
        Schema::PRIMARY_KEY => ['id'],
        Schema::FIND_BY_KEYS => ['id'],
        Schema::COLUMNS => [
            'id' => 'id',
            'target_group_id' => 'target_group_id',
            'monitorName' => 'monitor_name',
        ],
        Schema::RELATIONS => [],
        Schema::TYPECAST => [
            'id' => Type\TargetId::class,
            'target_group_id' => Type\TargetGroupId::class,
            'monitorName' => Type\MonitorName::class,
        ],
        Schema::TYPECAST_HANDLER => [TypeCaster::class, Typecast::class],
    ],

    'pingMonitor' => [
        Schema::ENTITY => PingMonitor::class,
        Schema::DATABASE => 'default',
        Schema::TABLE => 'ping_monitors',
        Schema::PARENT => Target::class,
        Schema::PRIMARY_KEY => ['id'],
        Schema::FIND_BY_KEYS => ['id'],
        Schema::COLUMNS => [
            'id' => 'id',
            'hostname' => 'hostname',
            'monitorInterval' => 'monitor_interval',
        ],
        Schema::RELATIONS => [],
        Schema::TYPECAST => [
            // 'id' => Type\PingMonitorId::class,
            'hostname' => Type\PublicHostname::class,
            'monitorInterval' => Type\MonitorInterval::class,
        ],
        Schema::TYPECAST_HANDLER => [TypeCaster::class, Typecast::class],
    ],

    'targetGroup' => [
        Schema::ENTITY => TargetGroup::class,
        Schema::DATABASE => 'default',
        Schema::TABLE => 'target_groups',
        Schema::PRIMARY_KEY => ['id'],
        Schema::FIND_BY_KEYS => ['id'],
        Schema::COLUMNS => [
            'id' => 'id',
            'name' => 'name',
        ],
        Schema::RELATIONS => [
            'targets' => [
                Relation::TYPE => Relation::HAS_MANY,
                Relation::TARGET => 'target',
                Relation::LOAD => Relation::LOAD_PROMISE,
                Relation::SCHEMA => [
                    Relation::CASCADE => true,
                    Relation::NULLABLE => false,
                    Relation::WHERE => [],
                    Relation::ORDER_BY => [],
                    Relation::INNER_KEY => ['id'],
                    Relation::OUTER_KEY => 'target_group_id',
                ],
            ],
            'manyTargets' => [
                Relation::TYPE => Relation::MANY_TO_MANY,
                Relation::TARGET => 'target',
                Relation::LOAD => Relation::LOAD_PROMISE,
                Relation::SCHEMA => [
                    Relation::CASCADE => true,
                    Relation::NULLABLE => false,
                    Relation::ORDER_BY => [],
                    Relation::THROUGH_ENTITY => Pivot::class,
                    Relation::INNER_KEY => 'id',
                    Relation::THROUGH_INNER_KEY => 'targetGroupId',
                    Relation::THROUGH_OUTER_KEY => 'targetId',
                    Relation::OUTER_KEY => 'id',
                ],
            ],
        ],
        Schema::TYPECAST => [
            'id' => Type\TargetGroupId::class,
            'name' => Type\TargetGroupName::class,
        ],
        Schema::TYPECAST_HANDLER => [TypeCaster::class, Arcano\ORM\Parser\Typecast::class],
    ],

    'pivot' => [
        Schema::ENTITY => Pivot::class,
        Schema::DATABASE => 'default',
        Schema::TABLE => 'pivots',
        Schema::PRIMARY_KEY => ['targetId', 'targetGroupId'],
        Schema::COLUMNS => [
            'targetId' => 'target_id',
            'targetGroupId' => 'target_group_id',
            'hash' => 'hash',
        ],
        Schema::RELATIONS => [],
        Schema::TYPECAST => [
            'targetId' => Type\TargetId::class,
            'targetGroupId' => Type\TargetGroupId::class,
            'hash' => Type\Hash::class,
        ],
        Schema::TYPECAST_HANDLER => [TypeCaster::class, Arcano\ORM\Parser\Typecast::class],
    ],

    // 'pivotChild' => [
    //     Schema::ENTITY => Pivot::class,
    //     Schema::DATABASE => 'default',
    //     Schema::TABLE => 'pivot_children',
    //     Schema::PRIMARY_KEY => ['targetId', 'targetGroupId'],
    //     Schema::PARENT => 'pivot',
    //     Schema::COLUMNS => [
    //         'targetId' => 'target_id',
    //         'targetGroupId' => 'target_group_id',
    //         'rate' => 'rate',
    //     ],
    //     Schema::RELATIONS => [],
    //     Schema::TYPECAST => [
    //         'targetId' => Type\TargetId::class,
    //         'targetGroupId' => Type\TargetGroupId::class,
    //         'rate' => Type\Rate::class,
    //     ],
    //     Schema::TYPECAST_HANDLER => [TypeCaster::class, Arcano\ORM\Parser\Typecast::class],
    // ],
];

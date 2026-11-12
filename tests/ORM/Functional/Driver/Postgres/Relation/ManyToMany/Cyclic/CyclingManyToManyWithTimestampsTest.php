<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Postgres\Relation\ManyToMany\Cyclic;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\ManyToMany\Cyclic\CyclingManyToManyWithTimestampsTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class CyclingManyToManyWithTimestampsTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

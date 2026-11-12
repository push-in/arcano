<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Postgres\Select;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Select\SelectAggregateTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class SelectAggregateTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

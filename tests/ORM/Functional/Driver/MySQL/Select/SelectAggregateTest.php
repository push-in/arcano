<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Select;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Select\SelectAggregateTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class SelectAggregateTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

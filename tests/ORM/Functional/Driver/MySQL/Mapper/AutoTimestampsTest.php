<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Mapper;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Mapper\AutoTimestampsTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class AutoTimestampsTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

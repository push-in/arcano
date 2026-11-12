<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\MySQL\Driver;

// phpcs:ignore
use Arcano\Database\Tests\Functional\Driver\Common\Driver\StatementTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class StatementTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

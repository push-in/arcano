<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\SQLite\Driver;

// phpcs:ignore
use Arcano\Database\Tests\Functional\Driver\Common\Driver\StatementTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class StatementTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

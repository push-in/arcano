<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\SQLite\Driver;

// phpcs:ignore
use Arcano\Database\Tests\Functional\Driver\Common\Driver\ReadonlyTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class ReadonlyTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

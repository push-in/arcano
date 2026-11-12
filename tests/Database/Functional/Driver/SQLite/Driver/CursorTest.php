<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\SQLite\Driver;

// phpcs:ignore
use Arcano\Database\Tests\Functional\Driver\Common\Driver\CursorTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class CursorTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

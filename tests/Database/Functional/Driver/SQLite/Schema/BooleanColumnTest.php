<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\SQLite\Schema;

// phpcs:ignore
use Arcano\Database\Tests\Functional\Driver\Common\Schema\BooleanColumnTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class BooleanColumnTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

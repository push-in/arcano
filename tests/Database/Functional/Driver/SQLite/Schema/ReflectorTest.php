<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\SQLite\Schema;

// phpcs:ignore
use Arcano\Database\Tests\Functional\Driver\Common\Schema\ReflectorTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class ReflectorTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

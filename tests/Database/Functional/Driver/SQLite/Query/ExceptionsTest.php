<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\SQLite\Query;

// phpcs:ignore
use Arcano\Database\Tests\Functional\Driver\Common\Query\ExceptionsTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class ExceptionsTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

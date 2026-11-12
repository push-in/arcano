<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\Postgres;

// phpcs:ignore
use Arcano\Database\Tests\Functional\Driver\Common\DatabaseTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class DatabaseTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

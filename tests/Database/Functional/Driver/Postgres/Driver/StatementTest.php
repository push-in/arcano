<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\Postgres\Driver;

// phpcs:ignore
use Arcano\Database\Tests\Functional\Driver\Common\Driver\StatementTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class StatementTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

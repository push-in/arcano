<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\Postgres\Connection;

// phpcs:ignore
use Arcano\Database\Tests\Functional\Driver\Common\Connection\TransactionDisconnectingTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class TransactionDisconnectingTest extends CommonClass
{
    public const DRIVER = 'postgres-mock';
}

<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\SQLServer\Connection;

// phpcs:ignore
use Arcano\Database\Tests\Functional\Driver\Common\Connection\TransactionDisconnectingTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class TransactionDisconnectingTest extends CommonClass
{
    public const DRIVER = 'sqlserver-mock';
}

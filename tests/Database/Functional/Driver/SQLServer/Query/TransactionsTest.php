<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\SQLServer\Query;

// phpcs:ignore
use Arcano\Database\Tests\Functional\Driver\Common\Query\TransactionsTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class TransactionsTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

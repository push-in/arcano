<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\SQLite\Connection;

// phpcs:ignore
use Arcano\Database\Exception\StatementException\ConnectionException;
use Arcano\Database\Tests\Functional\Driver\Common\Connection\ConnectionExceptionTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class ConnectionExceptionTest extends CommonClass
{
    public const DRIVER = 'sqlite-mock';

    /**
     * There is no cases for ConnectionException.
     *
     * @see \Arcano\Database\Driver\SQLite\SQLiteDriver::mapException()
     */
    public function reconnectableExceptionsProvider(): iterable
    {
        return [];
    }
}

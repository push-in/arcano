<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Unit\Config;

use Arcano\Database\Config\SQLServer\DsnConnectionConfig;
use Arcano\Database\Config\SQLServerDriverConfig;
use Arcano\Database\Driver\SQLServer\SQLServerDriver;
use PHPUnit\Framework\TestCase;

final class SQLServerDriverConfigTest extends TestCase
{
    public function testSetState(): void
    {
        $config = new SQLServerDriverConfig(
            connection: new DsnConnectionConfig($dsn = 'dsn'),
            driver: $driver = SQLServerDriver::class,
            reconnect: $reconnect = true,
            timezone: $timezone = 'UTC',
            queryCache: $queryCache = true,
            readonlySchema: $readonlySchema = false,
            readonly: $readonly = false,
        );

        $exported = \var_export($config, true);

        /** @var SQLServerDriverConfig $recoveredConfig */
        eval('$recoveredConfig = ' . $exported . ';');

        $this->assertInstanceOf(DsnConnectionConfig::class, $recoveredConfig->connection);
        $this->assertSame("sqlsrv:$dsn", $recoveredConfig->connection->getDsn());
        $this->assertSame($driver, $recoveredConfig->driver);
        $this->assertSame($reconnect, $recoveredConfig->reconnect);
        $this->assertSame($timezone, $recoveredConfig->timezone);
        $this->assertSame($queryCache, $recoveredConfig->queryCache);
        $this->assertSame($readonlySchema, $recoveredConfig->readonlySchema);
        $this->assertSame($readonly, $recoveredConfig->readonly);
    }
}

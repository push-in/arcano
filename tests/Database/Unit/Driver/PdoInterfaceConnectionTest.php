<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Unit\Driver;

use Arcano\Database\Config\SQLiteDriverConfig;
use Arcano\Database\Driver\PDOInterface;
use Arcano\Database\Driver\SQLite\SQLiteDriver;
use Arcano\Database\Tests\Stub\Driver\TestPDO;
use PHPUnit\Framework\TestCase;

/** A driver may connect through any PDOInterface implementation, not only \PDO. */
final class PdoInterfaceConnectionTest extends TestCase
{
    public function testADriverConnectsThroughAPdoInterfaceImplementation(): void
    {
        $driver = PdoInterfaceSQLiteDriver::create(new SQLiteDriverConfig());

        $driver->connect();

        self::assertTrue($driver->isConnected());
        self::assertSame(1, (int) $driver->query('SELECT 1')->fetchColumn());
        self::assertInstanceOf(TestPDO::class, $driver->connection());
    }
}

final class PdoInterfaceSQLiteDriver extends SQLiteDriver
{
    private int $exceptions = 0;

    public function connection(): \PDO|PDOInterface
    {
        return $this->getPDO();
    }

    protected function createPDO(): \PDO|PDOInterface
    {
        return new TestPDO(new \PDO('sqlite::memory:'), $this->exceptions, null);
    }
}

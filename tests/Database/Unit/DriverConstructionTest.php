<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Unit;

use Arcano\Database\Config\DatabaseConfig;
use Arcano\Database\Config\DriverConfig;
use Arcano\Database\Config\SQLiteDriverConfig;
use Arcano\Database\DatabaseManager;
use Arcano\Database\Driver\SQLite\SQLiteDriver;
use PHPUnit\Framework\TestCase;

/** Listing the drivers must not build throwaway driver objects on every call. */
final class DriverConstructionTest extends TestCase
{
    public function testGetDriversBuildsEachDriverOnce(): void
    {
        CountingSQLiteDriver::$created = 0;
        $manager = new DatabaseManager(new DatabaseConfig([
            'default' => 'default',
            'databases' => ['default' => ['connection' => 'sqlite']],
            'connections' => ['sqlite' => new SQLiteDriverConfig(driver: CountingSQLiteDriver::class)],
        ]));

        $first = $manager->getDrivers();
        $manager->getDrivers();
        $manager->getDrivers();

        self::assertCount(1, $first);
        self::assertSame(1, CountingSQLiteDriver::$created);
    }
}

final class CountingSQLiteDriver extends SQLiteDriver
{
    public static int $created = 0;

    public static function create(DriverConfig $config): static
    {
        ++self::$created;
        return parent::create($config);
    }
}

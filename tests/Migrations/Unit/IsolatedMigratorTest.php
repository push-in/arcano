<?php

declare(strict_types=1);

namespace Arcano\Migrations\Tests\Unit;

use Arcano\Database\Config\DatabaseConfig;
use Arcano\Database\Database;
use Arcano\Database\DatabaseManager;
use Arcano\Database\Driver\DriverInterface;
use Arcano\Migrations\Config\MigrationConfig;
use Arcano\Migrations\Migrator;
use Arcano\Migrations\RepositoryInterface;
use PHPUnit\Framework\TestCase;

final class IsolatedMigratorTest extends TestCase
{
    public function testReadonlyDriversMustNotBeUsed()
    {
        $dbal = new DatabaseManager(new DatabaseConfig());
        $dbal->addDatabase(
            new Database(
                'foo',
                '',
                $driver = $this->createMock(DriverInterface::class),
            ),
        );
        $driver->expects($this->atLeastOnce())->method('isReadonly')->willReturn(true);
        $driver
            ->expects($this->any())
            ->method($this->callback(fn($name) => $name !== 'isReadonly'))
            ->willThrowException(new \RuntimeException('Unexpected method call'));

        $repository = $this->createMock(RepositoryInterface::class);

        $migrator = new Migrator(
            new MigrationConfig([]),
            $dbal,
            $repository,
        );

        $migrator->isConfigured();
    }
}

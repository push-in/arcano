<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\Postgres;

use Arcano\Database\Config\Postgres\TcpConnectionConfig;
use Arcano\Database\Config\PostgresDriverConfig;
use Arcano\Database\Database;
use Arcano\Database\Driver\DriverInterface;
use Arcano\Database\Driver\Postgres\PostgresDriver;
use Arcano\Database\Driver\Postgres\Schema\PostgresTable;
use Arcano\Database\Schema\AbstractTable;

trait Helpers
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpSchemas();
    }

    protected function dropUserSchema(): void
    {
        $driver = $this->getDriver();
        $query = "SELECT schema_name FROM information_schema.schemata WHERE schema_name = 'postgres'";
        if ($driver->query($query)->fetch()) {
            $driver->query('DROP SCHEMA postgres CASCADE');
        }
    }

    protected function setUpSchemas(): void
    {
        $driver = $this->getDriver();
        $schemas = ['public', 'schema1', 'schema2', 'postgres'];
        foreach ($schemas as $schema) {
            $query = "SELECT schema_name
                        FROM information_schema.schemata
                        WHERE schema_name = '{$schema}'";
            if ($driver->query($query)->fetch()) {
                $driver->query("DROP SCHEMA {$schema} CASCADE");
            }

            $driver->query("CREATE SCHEMA {$schema}");
        }
    }

    protected function fetchSchema(AbstractTable $table): AbstractTable
    {
        return $this->schema($table->getFullName());
    }

    private function createTable(DriverInterface $driver, string $name): PostgresTable
    {
        $db = new Database('default', '', $driver);

        $schema = $db->table($name)->getSchema();

        $schema->primary('id');
        $schema->save();

        return $schema;
    }

    private function getDriver($schema = null, ?string $defaultSchema = null): DriverInterface
    {
        $options = new PostgresDriverConfig(
            connection: new TcpConnectionConfig(
                database: 'spiral',
                host: '127.0.0.1',
                port: 15432,
                user: 'postgres',
                password: 'YourStrong!Passw0rd',
            ),
            schema: \array_filter([$defaultSchema, ...\array_values((array) $schema)]),
        );

        $driver = PostgresDriver::create($options);
        $driver->connect();

        return $driver;
    }
}

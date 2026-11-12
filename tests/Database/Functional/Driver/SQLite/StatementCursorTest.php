<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\SQLite;

use Arcano\Database\Config\SQLite\FileConnectionConfig;
use Arcano\Database\Config\SQLiteDriverConfig;
use Arcano\Database\Database;
use Arcano\Database\Driver\SQLite\SQLiteDriver;
use PHPUnit\Framework\TestCase;

/**
 * @group driver
 * @group driver-sqlite
 */
final class StatementCursorTest extends TestCase
{
    private string $file;

    private Database $database;

    protected function setUp(): void
    {
        $this->file = \tempnam(\sys_get_temp_dir(), 'arcano-cursor');
        $driver = SQLiteDriver::create(new SQLiteDriverConfig(connection: new FileConnectionConfig($this->file)));
        $this->database = new Database('default', '', $driver);
        $this->database->execute('CREATE TABLE items (id INTEGER PRIMARY KEY, name TEXT)');
        $this->database->execute("INSERT INTO items (name) VALUES ('a'), ('b'), ('c')");
    }

    protected function tearDown(): void
    {
        $this->database->getDriver()->disconnect();
        @\unlink($this->file);
    }

    public function testPartlyReadCachedStatementsReleaseTheFile(): void
    {
        $this->assertTrue($this->database->hasTable('items'));
        $this->assertSame('a', $this->database->query('SELECT name FROM items ORDER BY id')->fetchColumn());

        $other = new \PDO('sqlite:' . $this->file);
        $other->setAttribute(\PDO::ATTR_TIMEOUT, 0);
        $other->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $this->assertSame(1, $other->exec("INSERT INTO items (name) VALUES ('d')"));
    }

    public function testAnEarlierWrapperKeepsTheNewerCursorOpen(): void
    {
        $first = $this->database->query('SELECT name FROM items ORDER BY id');
        $second = $this->database->query('SELECT name FROM items ORDER BY id');
        $this->assertSame($first->getPDOStatement(), $second->getPDOStatement());

        unset($first);

        $this->assertSame(['a', 'b', 'c'], \array_column($second->fetchAll(), 'name'));
    }
}

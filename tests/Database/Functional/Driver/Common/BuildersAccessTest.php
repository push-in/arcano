<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\Common;

use Arcano\Database\Driver\CompilerInterface;
use Arcano\Database\Driver\Driver;
use Arcano\Database\Query\DeleteQuery;
use Arcano\Database\Query\InsertQuery;
use Arcano\Database\Query\SelectQuery;
use Arcano\Database\Query\UpdateQuery;
use Arcano\Database\Schema\AbstractTable;
use Arcano\Database\Table;

abstract class BuildersAccessTest extends BaseTest
{
    public function testDriverAccess(): void
    {
        $this->assertInstanceOf(Driver::class, $this->db()->getDriver());
    }

    public function testTableAccess(): void
    {
        $this->assertInstanceOf(
            Table::class,
            $this->db()->table('sample'),
        );
    }

    public function testTableSchemaAccess(): void
    {
        $this->assertInstanceOf(
            AbstractTable::class,
            $this->db()->table('sample')->getSchema(),
        );
    }

    public function testTableDatabaseAccess(): void
    {
        $this->assertEquals(
            $this->db(),
            $this->db()->table('sample')->getDatabase(),
        );
    }

    public function testCompilerAccess(): void
    {
        $this->assertInstanceOf(
            CompilerInterface::class,
            $this->db()->getDriver()->getQueryCompiler(''),
        );
    }

    //via db

    public function testSelectQueryAccess(): void
    {
        $this->assertInstanceOf(SelectQuery::class, $this->db()->select());
    }

    public function testInsertQueryAccess(): void
    {
        $this->assertInstanceOf(InsertQuery::class, $this->db()->insert());
    }

    public function testUpdateQueryAccess(): void
    {
        $this->assertInstanceOf(UpdateQuery::class, $this->db()->update());
    }

    public function testDeleteQueryAccess(): void
    {
        $this->assertInstanceOf(DeleteQuery::class, $this->db()->delete());
    }

    //via table

    public function testSelectQueryAccessThoughtTable(): void
    {
        $this->assertInstanceOf(SelectQuery::class, $this->db()->table('sample')->select());
    }

    public function testSelectQueryAccessThoughtTableIterator(): void
    {
        $this->assertInstanceOf(SelectQuery::class, $this->db()->table('sample')->getIterator());
    }

    public function testUpdateQueryAccessThoughtTable(): void
    {
        $this->assertInstanceOf(UpdateQuery::class, $this->db()->table('sample')->update());
    }

    public function testDeleteQueryAccessThoughtTable(): void
    {
        $this->assertInstanceOf(DeleteQuery::class, $this->db()->table('sample')->delete());
    }
}

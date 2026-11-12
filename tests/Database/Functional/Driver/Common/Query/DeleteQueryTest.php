<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\Common\Query;

use Arcano\Database\Query\DeleteQuery;
use Arcano\Database\Tests\Functional\Driver\Common\BaseTest;

abstract class DeleteQueryTest extends BaseTest
{
    public function testQueryInstance(): void
    {
        $this->assertInstanceOf(
            DeleteQuery::class,
            $this->database->delete(),
        );

        $this->assertInstanceOf(
            DeleteQuery::class,
            $this->database->table('table')->delete(),
        );

        $this->assertInstanceOf(
            DeleteQuery::class,
            $this->database->table->delete(),
        );
    }

    public function testCompileQuery(): void
    {
        $delete = $this->db()->delete('table')
            ->where(['name' => 'Antony']);

        $this->assertSameQuery(
            'DELETE FROM {table} WHERE {name} = \'Antony\'',
            (string) $delete,
        );
    }

    public function testSimpleDeletion(): void
    {
        $delete = $this->database->delete()->from('table');

        $this->assertSameQuery(
            'DELETE FROM {table}',
            $delete,
        );
    }

    public function testDeletionWithWhere(): void
    {
        $delete = $this->database->delete()->from('table')->where('name', 'Anton');

        $this->assertSameQuery(
            'DELETE FROM {table} WHERE {name} = ?',
            $delete,
        );
    }

    public function testDeletionWithShortWhere(): void
    {
        $delete = $this->database->delete()->from('table')->where(['name' => 'Anton']);

        $this->assertSameQuery(
            'DELETE FROM {table} WHERE {name} = ?',
            $delete,
        );
    }
}

<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Unit\Driver;

use Arcano\Database\Driver\CompilerCache;
use Arcano\Database\Driver\Postgres\PostgresCompiler;
use Arcano\Database\Injection\Fragment;
use Arcano\Database\Query\QueryParameters;
use PHPUnit\Framework\TestCase;

final class CompilerCacheTest extends TestCase
{
    public function testHashInsertQueryWithReturningFragment(): void
    {
        $compiler = new CompilerCache(new PostgresCompiler());
        $ref = new \ReflectionMethod($compiler, 'hashInsertQuery');
        $ref->setAccessible(true);

        $this->assertSame(
            'i_some_tablename_full_name_rname_"full_name" as "fullName"P?',
            $ref->invoke($compiler, new QueryParameters(), [
                'table' => 'some_table',
                'columns' => ['name', 'full_name'],
                'values' => ['Foo'],
                'return' => ['name', new Fragment('"full_name" as "fullName"')],
            ]),
        );
    }
}

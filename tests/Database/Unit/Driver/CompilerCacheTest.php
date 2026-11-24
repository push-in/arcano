<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Unit\Driver;

use Arcano\Database\Driver\CompilerCache;
use Arcano\Database\Driver\Postgres\PostgresCompiler;
use Arcano\Database\Injection\Fragment;
use Arcano\Database\Query\ActiveQuery;
use Arcano\Database\Query\InsertQuery;
use Arcano\Database\Query\OnConflict;
use Arcano\Database\Query\QueryParameters;
use Arcano\Database\Query\SelectQuery;
use Arcano\Database\Query\UpdateQuery;
use PHPUnit\Framework\TestCase;

final class CompilerCacheTest extends TestCase
{
    public function testHashInsertQueryWithReturningFragment(): void
    {
        $compiler = new CompilerCache(new PostgresCompiler());
        $ref = new \ReflectionMethod($compiler, 'hashInsertQuery');
        $ref->setAccessible(true);

        $this->assertSame(
            'i_10:some_tablec4:name9:full_namer4:name25:"full_name" as "fullName"vP?',
            $ref->invoke($compiler, new QueryParameters(), [
                'table' => 'some_table',
                'columns' => ['name', 'full_name'],
                'values' => ['Foo'],
                'return' => ['name', new Fragment('"full_name" as "fullName"')],
            ]),
        );
    }

    /**
     * Cache keys used to concatenate names without a boundary, so a second query whose names
     * merely re-split the same characters got the first query's SQL (wrong table or columns).
     *
     * @return iterable<string, array{\Closure(): ActiveQuery, \Closure(): ActiveQuery}>
     */
    public static function collidingQueries(): iterable
    {
        yield 'insert table/column' => [
            static fn() => (new InsertQuery('ta'))->columns('b')->values(['x']),
            static fn() => (new InsertQuery('t'))->columns('ab')->values(['x']),
        ];
        yield 'insert column split' => [
            static fn() => (new InsertQuery('t'))->columns(['a_b'])->values(['x']),
            static fn() => (new InsertQuery('t'))->columns(['a', 'b'])->values(['x', 'y']),
        ];
        yield 'select table/column' => [
            static fn() => (new SelectQuery())->from('users')->columns('id'),
            static fn() => (new SelectQuery())->from('user')->columns('sid'),
        ];
        yield 'select join alias/table' => [
            static fn() => (new SelectQuery())->from('t')->join('LEFT', 'ab')->on('ab.id', 't.id'),
            static fn() => (new SelectQuery())->from('t')->join('LEFT', 'a')->on('a.bid', 't.id'),
        ];
        yield 'upsert update list' => [
            static fn() => (new InsertQuery('t'))->columns('a', 'b')->values(['x', 'y'])
                ->onConflict(OnConflict::target('a')->doUpdate(['a_b'])),
            static fn() => (new InsertQuery('t'))->columns('a', 'b')->values(['x', 'y'])
                ->onConflict(OnConflict::target('a')->doUpdate(['a', 'b'])),
        ];
        yield 'upsert target' => [
            static fn() => (new InsertQuery('t'))->columns('a', 'b')->values(['x', 'y'])
                ->onConflict(OnConflict::target(['a_b'])),
            static fn() => (new InsertQuery('t'))->columns('a', 'b')->values(['x', 'y'])
                ->onConflict(OnConflict::target('a', 'b')),
        ];
    }

    /**
     * @dataProvider collidingQueries
     */
    public function testNamesThatResplitTheSameCharactersDoNotShareCachedSql(\Closure $first, \Closure $second): void
    {
        $cache = new CompilerCache(new PostgresCompiler());
        $cache->compile(new QueryParameters(), '', $first());

        $expected = (new PostgresCompiler())->compile(new QueryParameters(), '', $second());

        $this->assertSame($expected, $cache->compile(new QueryParameters(), '', $second()));
    }

    public function testNumericStringColumnNamesCompile(): void
    {
        // PHP turns the "2024" array key into an int; the compiler used to reject it.
        $sql = (new PostgresCompiler())->compile(
            new QueryParameters(),
            '',
            (new UpdateQuery('t'))->set('2024', 1),
        );

        $this->assertSame("UPDATE \"t\"\nSET \"2024\" = ?", $sql);
    }

}

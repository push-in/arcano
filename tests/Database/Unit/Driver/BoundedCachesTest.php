<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Unit\Driver;

use Arcano\Database\Config\SQLite\MemoryConnectionConfig;
use Arcano\Database\Config\SQLiteDriverConfig;
use Arcano\Database\Driver\CompilerCache;
use Arcano\Database\Driver\Driver;
use Arcano\Database\Driver\Postgres\PostgresCompiler;
use Arcano\Database\Driver\SQLite\SQLiteDriver;
use Arcano\Database\Query\QueryParameters;
use Arcano\Database\Query\SelectQuery;
use PHPUnit\Framework\TestCase;

/**
 * A long-lived worker sees an open-ended number of statements (IN lists of every length,
 * raw fragments with values): the prepared statement and compiled SQL caches must stay bounded.
 */
final class BoundedCachesTest extends TestCase
{
    public function testThePreparedStatementCacheIsBounded(): void
    {
        $driver = SQLiteDriver::create(new SQLiteDriverConfig(new MemoryConnectionConfig(), queryCache: true));
        for ($index = 0; $index < Driver::QUERY_CACHE_LIMIT + 200; $index++) {
            $driver->query("SELECT {$index}")->fetchAll();
        }
        $cache = (new \ReflectionProperty(Driver::class, 'queryCache'))->getValue($driver);
        self::assertLessThanOrEqual(Driver::QUERY_CACHE_LIMIT, \count($cache));
        // The most recent statements are still cached.
        self::assertArrayHasKey('SELECT ' . (Driver::QUERY_CACHE_LIMIT + 199), $cache);
    }

    public function testTheCompiledQueryCacheIsBounded(): void
    {
        $compiler = new CompilerCache(new PostgresCompiler());
        for ($size = 1; $size <= CompilerCache::LIMIT + 300; $size++) {
            $compiler->compile(
                new QueryParameters(),
                '',
                (new SelectQuery())->from('users')->where('id', 'IN', \range(1, $size)),
            );
        }
        $cache = (new \ReflectionProperty(CompilerCache::class, 'cache'))->getValue($compiler);
        self::assertLessThanOrEqual(CompilerCache::LIMIT, \count($cache));
    }
}

<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Unit\Command;

use Arcano\Database\Config\SQLite\MemoryConnectionConfig;
use Arcano\Database\Config\SQLiteDriverConfig;
use Arcano\Database\Database;
use Arcano\Database\Driver\SQLite\SQLiteDriver;
use Arcano\ORM\Command\Database\StatementPlans;
use PHPUnit\Framework\TestCase;

/**
 * Insert and update plans are cached per database for the life of the worker: an open-ended
 * number of column sets must not grow the cache without bound.
 */
final class StatementPlansTest extends TestCase
{
    public function testThePlanCacheIsBoundedPerDatabase(): void
    {
        $db = new Database('default', '', SQLiteDriver::create(new SQLiteDriverConfig(new MemoryConnectionConfig())));
        $db->execute('CREATE TABLE items (id INTEGER PRIMARY KEY, value TEXT)');
        $limit = (new \ReflectionClassConstant(StatementPlans::class, 'MAX_PLANS'))->getValue();

        for ($index = 0; $index < $limit + 100; $index++) {
            // Every column set makes a new plan key; the plan is cached before it runs, so a
            // column the table lacks still fills the cache.
            try {
                StatementPlans::update($db, 'items', ['value' => 'x'], ["c{$index}" => 1]);
            } catch (\Throwable) {
            }
        }

        $plans = (new \ReflectionProperty(StatementPlans::class, 'plans'))->getValue();
        self::assertInstanceOf(\WeakMap::class, $plans);
        self::assertLessThanOrEqual($limit, \count($plans[$db]));

        // Past the cap a new column set is not planned: the caller uses the query builder.
        self::assertSame([false, null], StatementPlans::insert($db, 'items', ['id' => 1, 'value' => 'a'], []));
        self::assertNull(StatementPlans::update($db, 'items', ['value' => 'b'], ['id' => 1]));
    }
}

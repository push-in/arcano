<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\Postgres\Query;

// phpcs:ignore
use Arcano\Database\Injection\Fragment;
use Arcano\Database\Tests\Functional\Driver\Common\Query\SelectWithJoinQueryTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class SelectWithJoinQueryTest extends CommonClass
{
    public const DRIVER = 'postgres';

    public function testCacheLeftJoinLateral(): void
    {
        $compiler = $this->database->select()->getDriver()->getQueryCompiler();

        $ref = new \ReflectionProperty($compiler, 'cache');
        $ref->setAccessible(true);
        $ref->setValue($compiler, []);

        $select = $this->database->select()
            ->from('temperature as t')
            ->where('t.date', '2022-01-05')
            ->join(
                type: 'LEFT JOIN LATERAL',
                outer: $this->database->select()
                    ->from('humidity')
                    ->where('h.date', '<=', 't.date'),
                alias: 'h',
                on: new Fragment('true'),
            );

        $select->sqlStatement();

        // Verify that the join name has a correct format in the cache
        $cache = $ref->getValue($compiler);
        $this->assertCount(1, $cache);
        $this->assertStringContainsString("j1:h11:LEFTLATERALp_0:", \array_key_first($cache));
    }
}

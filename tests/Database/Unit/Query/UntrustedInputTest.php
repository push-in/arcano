<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Unit\Query;

use Arcano\Database\Exception\BuilderException;
use Arcano\Database\Injection\Expression;
use Arcano\Database\Injection\Fragment;
use Arcano\Database\Query\SelectQuery;
use PHPUnit\Framework\TestCase;

/**
 * Strings in ORDER BY / GROUP BY are column names, never SQL (audit M10, B8).
 */
final class UntrustedInputTest extends TestCase
{
    public static function injectedSortKeys(): iterable
    {
        yield 'case subquery' => ['(SELECT(CASE(1)WHEN(1)THEN(SLEEP(5))END))'];
        yield 'function' => ['RAND()'];
        yield 'space' => ['id DESC, (SELECT 1)'];
        yield 'comment' => ['id--'];
        yield 'quote' => ['id"'];
        yield 'semicolon' => ['id;DROP TABLE users'];
        yield 'json path with quote' => ["data->x' OR 1=1"];
        yield 'empty' => [''];
    }

    /** @dataProvider injectedSortKeys */
    public function testOrderByRefusesExpressionsInPlainStrings(string $key): void
    {
        $this->expectException(BuilderException::class);
        (new SelectQuery())->from('users')->orderBy($key);
    }

    /** @dataProvider injectedSortKeys */
    public function testOrderByArrayKeysAreCheckedToo(string $key): void
    {
        $this->expectException(BuilderException::class);
        (new SelectQuery())->from('users')->orderBy([$key => 'ASC']);
    }

    /** @dataProvider injectedSortKeys */
    public function testGroupByRefusesExpressionsInPlainStrings(string $key): void
    {
        $this->expectException(BuilderException::class);
        (new SelectQuery())->from('users')->groupBy($key);
    }

    public function testColumnsPathsAndFragmentsAreAccepted(): void
    {
        $query = (new SelectQuery())->from('users')
            ->orderBy('id')
            ->orderBy('users.created_at', 'DESC')
            ->orderBy('@.level')
            ->orderBy('user:credentials.id')
            ->orderBy('settings->theme[0]')
            ->orderBy(new Fragment('RAND()'), null)
            ->groupBy('users.id')
            ->groupBy(new Expression('DATE(created_at)'));

        $this->assertCount(6, $query->getTokens()['orderBy']);
        $this->assertCount(2, $query->getTokens()['groupBy']);
    }

    public function testNegativeLimitIsRefused(): void
    {
        $this->expectException(BuilderException::class);
        (new SelectQuery())->limit(-1);
    }

    public function testNegativeOffsetIsRefused(): void
    {
        $this->expectException(BuilderException::class);
        (new SelectQuery())->offset(-1);
    }

    public function testZeroAndNullLimitsAreAccepted(): void
    {
        $query = (new SelectQuery())->limit(0)->offset(0)->limit(null)->offset(null);

        $this->assertNull($query->getLimit());
        $this->assertNull($query->getOffset());
    }
}

<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Select;

use Arcano\ORM\Mapper\Mapper;
use Arcano\ORM\Schema;
use Arcano\ORM\Select;
use Arcano\ORM\Tests\Functional\Driver\Common\BaseTest;
use Arcano\ORM\Tests\Fixtures\User;
use Arcano\ORM\Tests\Traits\TableTrait;
use Spiral\Pagination\Paginator;

abstract class PaginateTest extends BaseTest
{
    use TableTrait;

    public function testFetchData(): void
    {
        $selector = new Select($this->orm, User::class);

        $this->assertSame(100, $selector->count());
    }

    public function testPaginate(): void
    {
        $selector = new Select($this->orm, User::class);

        (new Paginator(10))->paginate($selector);

        $this->assertCount(10, $selector->fetchData());
    }

    public function testFetchPageReadsRowsAndCountWithOneStatement(): void
    {
        $select = (new Select($this->orm, User::class))->where('balance', '>=', 1000)->orderBy('id', 'DESC');

        $this->captureReadQueries();
        [$items, $total] = $select->fetchPage(7, 14);
        $this->assertNumReads(1);

        $this->assertSame(90, $total);
        $this->assertSame(
            \array_map(static fn (User $user): int => (int) $user->id, (clone $select)->limit(7)->offset(14)->fetchAll()),
            \array_map(static fn (User $user): int => (int) $user->id, $items),
        );
        $this->assertSame(86, (int) $items[0]->id);
        $this->assertSame(90, $select->count());
    }

    public function testFetchPageCachedStatementTakesTheValuesOfEachSelect(): void
    {
        foreach ([[0, 100], [5000, 50], [9900, 1], [9901, 0]] as [$balance, $expected]) {
            [$items, $total] = (new Select($this->orm, User::class))
                ->where('balance', '>=', $balance)
                ->orderBy('id')
                ->fetchPage(10, 0);
            $this->assertSame($expected, $total);
            $this->assertCount(\min(10, $expected), $items);
        }
    }

    public function testFetchPagePastTheLastRowStillCounts(): void
    {
        [$items, $total] = (new Select($this->orm, User::class))->orderBy('id')->fetchPage(10, 100);

        $this->assertSame([], $items);
        $this->assertSame(100, $total);
    }

    public function testFetchPageOfACustomizedSelect(): void
    {
        $select = (new Select($this->orm, User::class))->where(['balance' => ['<' => 500]])->orderBy('id');

        [$items, $total] = $select->fetchPage(2, 1);

        $this->assertSame(5, $total);
        $this->assertSame(['1@world.com', '2@world.com'], \array_map(static fn (User $user): string => $user->email, $items));
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->makeTable('user', [
            'id' => 'primary',
            'email' => 'string',
            'balance' => 'float',
        ]);

        for ($i = 0; $i < 100; $i++) {
            $this->getDatabase()->table('user')->insertMultiple(['email', 'balance'], [
                [$i . '@world.com', $i * 100],
            ]);
        }

        $this->orm = $this->withSchema(new Schema([
            User::class => [
                Schema::ROLE => 'user',
                Schema::MAPPER => Mapper::class,
                Schema::DATABASE => 'default',
                Schema::TABLE => 'user',
                Schema::PRIMARY_KEY => 'id',
                Schema::COLUMNS => ['id', 'email', 'balance'],
                Schema::SCHEMA => [],
                Schema::RELATIONS => [],
            ],
        ]));
    }
}

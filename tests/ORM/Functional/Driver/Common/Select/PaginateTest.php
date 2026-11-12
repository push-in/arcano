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

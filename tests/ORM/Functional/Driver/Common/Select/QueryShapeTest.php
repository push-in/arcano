<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Select;

use Arcano\ORM\Mapper\Mapper;
use Arcano\ORM\Schema;
use Arcano\ORM\Tests\Functional\Driver\Common\BaseTest;
use Arcano\ORM\Tests\Fixtures\User;
use Arcano\ORM\Tests\Traits\TableTrait;

/**
 * Recorded where/orderBy/limit calls and the statements cached per query shape.
 */
abstract class QueryShapeTest extends BaseTest
{
    use TableTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->makeTable('user', [
            'id' => 'primary',
            'email' => 'string',
            'balance' => 'float',
        ]);

        $this->getDatabase()->table('user')->insertMultiple(
            ['email', 'balance'],
            [
                ['hello@world.com', 100],
                ['another@world.com', 200],
                ['third@world.com', 300],
            ],
        );

        $this->orm = $this->withSchema(new Schema([
            User::class => [
                Schema::ROLE => 'user',
                Schema::MAPPER => Mapper::class,
                Schema::DATABASE => 'default',
                Schema::TABLE => 'user',
                Schema::PRIMARY_KEY => 'id',
                Schema::COLUMNS => ['id', 'email', 'balance'],
                Schema::TYPECAST => ['id' => 'int', 'balance' => 'float'],
                Schema::SCHEMA => [],
                Schema::RELATIONS => [],
            ],
        ]));
    }

    public function testSameShapeTakesNewValues(): void
    {
        $repository = $this->orm->getRepository(User::class);

        $this->assertSame([2, 3], $this->ids($repository->select()->where('balance', '>', 150)->orderBy('id')->fetchAll()));
        $this->assertSame([1, 2, 3], $this->ids($repository->select()->where('balance', '>', 50)->orderBy('id')->fetchAll()));
        $this->assertSame([3], $this->ids($repository->select()->where('balance', '>', 250)->orderBy('id')->fetchAll()));
    }

    public function testTwoValuesOrderAndLimit(): void
    {
        $select = $this->orm->getRepository(User::class)->select()
            ->where('balance', '>', 50)
            ->where('id', '<', 3)
            ->orderBy('id', 'DESC')
            ->limit(1);

        $this->assertSame([2], $this->ids($select->fetchAll()));
        $this->assertSame([1], $this->ids(
            $this->orm->getRepository(User::class)->select()->where('balance', '>', 50)->where('id', '<', 3)
                ->orderBy('id', 'DESC')->limit(1)->offset(1)->fetchAll(),
        ));
    }

    public function testStringAndLikeValues(): void
    {
        $repository = $this->orm->getRepository(User::class);

        $this->assertSame([1], $this->ids($repository->select()->where('email', 'hello@world.com')->fetchAll()));
        $this->assertSame([2], $this->ids($repository->select()->where('email', 'like', '%another%')->fetchAll()));
    }

    public function testRecordedCallsApplyOnOtherAccess(): void
    {
        $select = $this->orm->getRepository(User::class)->select()->where('balance', '>', 150);

        $this->assertSame(2, $select->count());
        $this->assertStringContainsString('balance', $select->buildQuery()->sqlStatement());
        $this->assertSame([2, 3], $this->ids($select->orderBy('id')->fetchAll()));
        $this->assertSame([3], $this->ids($select->andWhere('id', 3)->fetchAll()));
    }

    public function testClonesRecordSeparately(): void
    {
        $base = $this->orm->getRepository(User::class)->select()->where('balance', '>', 50);
        $narrow = (clone $base)->where('id', 2);

        $this->assertSame([2], $this->ids($narrow->fetchAll()));
        $this->assertSame([1, 2, 3], $this->ids($base->orderBy('id')->fetchAll()));
    }

    public function testHeapEntitiesAreReused(): void
    {
        $repository = $this->orm->getRepository(User::class);
        $first = $repository->select()->where('id', 1)->fetchAll()[0];
        $again = $repository->select()->where('id', 1)->fetchAll()[0];

        $this->assertSame($first, $again);
        $this->assertSame(100.0, $first->balance);
    }

    public function testOtherFormsStillWork(): void
    {
        $repository = $this->orm->getRepository(User::class);

        $this->assertSame([1, 3], $this->ids($repository->select()->where('id', 'in', new \Arcano\Database\Injection\Parameter([1, 3]))->orderBy('id')->fetchAll()));
        $this->assertSame([2], $this->ids($repository->select()->where(['email' => 'another@world.com'])->fetchAll()));
        $this->assertSame([1, 2], $this->ids($repository->select()->where('balance', '<', 250)->orderBy(['id' => 'ASC'])->fetchAll()));
    }

    public function testEveryPageOfAListingSharesOnePlan(): void
    {
        $repository = $this->orm->getRepository(User::class);
        $pages = [];
        foreach ([0, 1, 2, 5] as $offset) {
            [$users, $total] = $repository->select()->orderBy('id')->fetchPage(2, $offset);
            $pages[] = [$this->ids($users), $total];
        }
        foreach ([2, 3] as $limit) {
            $pages[] = $this->ids($repository->select()->orderBy('id')->limit($limit)->offset(1)->fetchAll());
        }

        $this->assertSame([[[1, 2], 3], [[2, 3], 3], [[3], 3], [[], 3], [2, 3], [2, 3]], $pages);
        $shapes = (new \ReflectionProperty(\Arcano\ORM\Select::class, 'shapes'))->getValue($repository->select());
        // Page + count from the first row, page + count past it, limit + offset: the numbers
        // themselves are statement parameters.
        $this->assertCount(3, $shapes);
    }

    public function testPageTotalWithoutOrderBy(): void
    {
        [$users, $total] = $this->orm->getRepository(User::class)->select()->where('balance', '>', 50)->fetchPage(1, 0);

        $this->assertCount(1, $users);
        $this->assertSame(3, $total);
        [$users, $total] = $this->orm->getRepository(User::class)->select()->where('balance', '>', 50)->fetchPage(2, 1);
        $this->assertCount(2, $users);
        $this->assertSame(3, $total);
    }

    public function testNegativeLimitAndOffsetAreRefused(): void
    {
        $select = $this->orm->getRepository(User::class)->select();

        try {
            $select->limit(-1);
            $this->fail('negative limit accepted');
        } catch (\InvalidArgumentException) {
        }
        $this->expectException(\InvalidArgumentException::class);
        $select->offset(-1);
    }

    public function testEmptyInListMatchesNothingAndEmptyNotInEverything(): void
    {
        $repository = $this->orm->getRepository(User::class);

        $this->assertSame([], $this->ids($repository->select()->where('id', 'in', new \Arcano\Database\Injection\Parameter([]))->fetchAll()));
        $this->assertSame([1, 2, 3], $this->ids(
            $repository->select()->where('id', 'not in', new \Arcano\Database\Injection\Parameter([]))->orderBy('id')->fetchAll(),
        ));
    }

    public function testSortKeyFromUserInputCannotInjectSql(): void
    {
        $this->expectException(\Arcano\Database\Exception\BuilderException::class);
        $this->orm->getRepository(User::class)->select()
            ->orderBy('(SELECT(CASE(1)WHEN(1)THEN(1)END))')
            ->fetchAll();
    }

    private function ids(iterable $users): array
    {
        $ids = [];
        foreach ($users as $user) {
            $ids[] = $user->id;
        }
        return $ids;
    }
}

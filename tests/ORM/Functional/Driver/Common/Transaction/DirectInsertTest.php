<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Transaction;

use Arcano\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Arcano\ORM\Heap\Heap;
use Arcano\ORM\Mapper\Mapper;
use Arcano\ORM\Relation;
use Arcano\ORM\Schema;
use Arcano\ORM\Tests\Fixtures\Comment;
use Arcano\ORM\Tests\Fixtures\User;
use Arcano\ORM\Tests\Functional\Driver\Common\BaseTest;
use Arcano\ORM\Tests\Traits\TableTrait;
use Arcano\ORM\Transaction\Runner;

/**
 * New entities with new has-many children are inserted without the pool, ending in
 * the same rows and heap state as the generic unit of work.
 */
abstract class DirectInsertTest extends BaseTest
{
    use TableTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->makeTable('user', ['id' => 'primary', 'email' => 'string', 'balance' => 'float']);
        $this->makeIndex('user', ['email'], true);
        $this->makeTable('comment', ['id' => 'primary', 'user_id' => 'integer,null', 'message' => 'string']);
        $this->makeFK('comment', 'user_id', 'user', 'id');

        $this->getDatabase()->table('user')->insertMultiple(
            ['email', 'balance'],
            [['hello@world.com', 100], ['another@world.com', 200]],
        );
        $this->getDatabase()->table('comment')->insertMultiple(['user_id', 'message'], [[1, 'msg 1']]);

        $this->orm = $this->withSchema(new Schema([
            User::class => [
                Schema::ROLE => 'user',
                Schema::MAPPER => Mapper::class,
                Schema::DATABASE => 'default',
                Schema::TABLE => 'user',
                Schema::PRIMARY_KEY => 'id',
                Schema::FIND_BY_KEYS => ['email'],
                Schema::COLUMNS => ['id', 'email', 'balance'],
                Schema::TYPECAST => ['id' => 'int', 'balance' => 'float'],
                Schema::SCHEMA => [],
                Schema::RELATIONS => [
                    'comments' => [
                        Relation::TYPE => Relation::HAS_MANY,
                        Relation::TARGET => Comment::class,
                        Relation::SCHEMA => [
                            Relation::CASCADE => true,
                            Relation::INNER_KEY => 'id',
                            Relation::OUTER_KEY => 'user_id',
                        ],
                    ],
                ],
            ],
            Comment::class => [
                Schema::ROLE => 'comment',
                Schema::MAPPER => Mapper::class,
                Schema::DATABASE => 'default',
                Schema::TABLE => 'comment',
                Schema::PRIMARY_KEY => 'id',
                Schema::COLUMNS => ['id', 'user_id', 'message'],
                Schema::TYPECAST => ['id' => 'int', 'user_id' => 'int'],
                Schema::SCHEMA => [],
                Schema::RELATIONS => [],
            ],
        ]));
    }

    public function testGraphMatchesTheGenericPath(): void
    {
        [$direct, $directComments] = $this->graph('direct', 2);
        $this->captureWriteQueries();
        $em = new EntityManager($this->orm);
        \array_map($em->persist(...), $direct);
        $em->run();
        $this->assertNumWrites(6);

        [$generic, $genericComments] = $this->graph('generic', 2);
        $em = new EntityManager($this->orm);
        \array_map($em->persist(...), $generic);
        $em->run(runner: Runner::innerTransaction());

        foreach ([0, 1] as $i) {
            $this->assertSame($this->describe($generic[$i], 2), $this->describe($direct[$i], 0));
            foreach ([0, 1] as $j) {
                $this->assertSame(
                    $this->describe($genericComments[$i][$j], 2, 4),
                    $this->describe($directComments[$i][$j], 0, 0),
                );
            }
        }

        $this->assertSame([[2, 3, 'direct 0.0'], [3, 3, 'direct 0.1'], [4, 4, 'direct 1.0'], [5, 4, 'direct 1.1']], $this->rows(2, 5));
        $this->assertSame([[6, 5, 'generic 0.0'], [7, 5, 'generic 0.1']], $this->rows(6, 7));
    }

    public function testInsertedGraphCanBeChangedAndReloaded(): void
    {
        [$users, $comments] = $this->graph('new', 1);
        (new EntityManager($this->orm))->persist($users[0])->run();

        $comments[0][1]->message = 'changed';
        // the node keeps the stored collection: a new one shows the removal
        $users[0]->comments = new ArrayCollection([$comments[0][1]]);
        $users[0]->balance = 9.0;
        (new EntityManager($this->orm))->persist($users[0])->run();

        $this->orm->getHeap()->clean();
        $user = $this->orm->getRepository(User::class)->findByPK($users[0]->id);
        $this->assertSame(9.0, $user->balance);
        $this->assertCount(1, $user->comments);
        $this->assertSame('changed', $user->comments[0]->message);
    }

    public function testFailureRollsBackAndCanRunAgain(): void
    {
        [$users, $comments] = $this->graph('dup', 2);
        $users[1]->email = 'hello@world.com'; // unique violation

        $em = new EntityManager($this->orm);
        $em->persist($users[0])->persist($users[1]);
        $state = $em->run(false);
        $this->assertFalse($state->isSuccess());
        $this->assertNull($users[0]->id);
        $this->assertNull($this->orm->getHeap()->get($users[0]));
        $this->assertSame(1, $this->getDatabase()->table('comment')->count());

        $users[1]->email = 'dup@world.com';
        (new EntityManager($this->orm))->persist($users[0])->persist($users[1])->run();
        $this->assertSame(5, $this->getDatabase()->table('comment')->count());
        $this->assertSame($users[1]->id, $comments[1][0]->user_id);
    }

    public function testManagedChildUsesThePool(): void
    {
        $comment = $this->orm->getRepository(Comment::class)->findByPK(1);
        $user = new User();
        $user->email = 'owner@world.com';
        $user->balance = 1.0;
        $user->comments->add($comment);
        (new EntityManager($this->orm))->persist($user)->run();

        $this->assertSame([[1, $user->id, 'msg 1']], $this->rows(1, 1));
    }

    public function testChildPersistedOnItsOwnUsesThePool(): void
    {
        [$users, $comments] = $this->graph('twice', 1);
        (new EntityManager($this->orm))->persist($users[0])->persist($comments[0][0])->run();

        $this->assertSame(3, $this->getDatabase()->table('comment')->count());
        $this->assertSame($users[0]->id, $comments[0][0]->user_id);
    }

    /**
     * @return array{list<User>, list<list<Comment>>}
     */
    private function graph(string $name, int $count): array
    {
        $users = $comments = [];
        for ($i = 0; $i < $count; $i++) {
            $user = $users[] = new User();
            $user->email = "{$name}{$i}@world.com";
            $user->balance = (float) $i;
            for ($j = 0; $j < 2; $j++) {
                $comment = $comments[$i][] = new Comment();
                $comment->message = "{$name} {$i}.{$j}";
                $user->comments->add($comment);
            }
        }

        return [$users, $comments];
    }

    /**
     * Node data and relations with ids shifted, so two graphs compare.
     */
    private function describe(object $entity, int $userShift, int $commentShift = 0): array
    {
        $node = $this->orm->getHeap()->get($entity);
        $this->assertNotNull($node);
        $data = $node->getData();
        isset($data['user_id']) and $data['user_id'] -= $userShift;
        $data['id'] -= $commentShift ?: $userShift;
        unset($data['email'], $data['message'], $data['balance']);
        $relations = [];
        foreach ((fn (): array => $this->relations)->call($node) as $relation => $value) {
            $relations[$relation] = \is_object($value) ? $value::class : \gettype($value);
        }

        return [$node->getStatus(), $data, $relations, $entity->id - ($commentShift ?: $userShift)];
    }

    private function rows(int $from, int $to): array
    {
        return \array_map(
            static fn (array $row): array => [(int) $row['id'], (int) $row['user_id'], $row['message']],
            $this->getDatabase()->table('comment')->select('id', 'user_id', 'message')
                ->where('id', '>=', $from)->where('id', '<=', $to)->orderBy('id')->fetchAll(),
        );
    }
}

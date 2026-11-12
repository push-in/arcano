<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Transaction;

use Arcano\ORM\EntityManager;
use Arcano\ORM\Heap\Heap;
use Arcano\ORM\Mapper\Mapper;
use Arcano\ORM\Relation;
use Arcano\ORM\Schema;
use Arcano\ORM\Tests\Fixtures\Comment;
use Arcano\ORM\Tests\Fixtures\User;
use Arcano\ORM\Tests\Functional\Driver\Common\BaseTest;
use Arcano\ORM\Tests\Traits\TableTrait;

/**
 * Stores of managed entities whose relations were never touched skip the pool.
 */
abstract class DirectUpdateTest extends BaseTest
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

    public function testChangedFieldsAreWrittenOnce(): void
    {
        $users = $this->orm->getRepository(User::class)->select()->orderBy('id')->fetchAll();
        $users[0]->balance = 150.0;

        $this->captureWriteQueries();
        $em = new EntityManager($this->orm);
        $em->persist($users[0])->persist($users[1])->run();
        $this->assertNumWrites(1);

        // the node has the stored values: nothing left to write
        $this->captureWriteQueries();
        (new EntityManager($this->orm))->persist($users[0])->persist($users[1])->run();
        $this->assertNumWrites(0);

        $this->orm->getHeap()->clean();
        $this->assertSame(150.0, $this->orm->getRepository(User::class)->findByPK(1)->balance);
        $this->assertSame(200.0, $this->orm->getRepository(User::class)->findByPK(2)->balance);
    }

    public function testChangedKeyIsReindexed(): void
    {
        $user = $this->orm->getRepository(User::class)->findByPK(1);
        $user->email = 'new@world.com';
        (new EntityManager($this->orm))->persist($user)->run();

        $heap = $this->orm->getHeap();
        \assert($heap instanceof Heap);
        $this->assertSame($user, $heap->find('user', ['email' => 'new@world.com']));
        $this->assertNull($heap->find('user', ['email' => 'hello@world.com']));
        $this->assertSame($user, $heap->find('user', ['id' => 1]));
    }

    public function testReadRelationUsesThePool(): void
    {
        $user = $this->orm->getRepository(User::class)->findByPK(1);
        $this->assertCount(1, $user->comments);

        $comment = new Comment();
        $comment->message = 'msg 2';
        $user->comments[] = $comment;
        $user->balance = 1.0;
        (new EntityManager($this->orm))->persist($user)->run();

        $this->orm->getHeap()->clean();
        $user = $this->orm->getRepository(User::class)->findByPK(1);
        $this->assertSame(1.0, $user->balance);
        $this->assertCount(2, $user->comments);
    }

    public function testFailureRollsBackAndKeepsTheNode(): void
    {
        $users = $this->orm->getRepository(User::class)->select()->orderBy('id')->fetchAll();
        $users[0]->balance = 1.0;
        $users[1]->email = 'hello@world.com'; // unique violation

        $state = (new EntityManager($this->orm))->persist($users[0])->persist($users[1])->run(false);
        $this->assertFalse($state->isSuccess());
        $this->assertNotNull($state->getLastError());

        $this->assertSame(
            100.0,
            (float) $this->getDatabase()->table('user')->select('balance')->where('id', 1)->run()->fetchColumn(),
        );

        $users[1]->email = 'other@world.com';
        $this->captureWriteQueries();
        (new EntityManager($this->orm))->persist($users[0])->persist($users[1])->run();
        $this->assertNumWrites(2);
    }

    public function testNewEntitiesMixWithManagedOnes(): void
    {
        $user = $this->orm->getRepository(User::class)->findByPK(2);
        $user->balance = 5.0;
        $new = new User();
        $new->email = 'third@world.com';
        $new->balance = 3.0;

        (new EntityManager($this->orm))->persist($user)->persist($new)->run();

        $this->orm->getHeap()->clean();
        $this->assertSame(5.0, $this->orm->getRepository(User::class)->findByPK(2)->balance);
        $this->assertSame(3.0, $this->orm->getRepository(User::class)->findOne(['email' => 'third@world.com'])->balance);
    }
}

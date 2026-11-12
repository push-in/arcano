<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Select;

use Arcano\ORM\EntityManager;
use Arcano\ORM\Mapper\Mapper;
use Arcano\ORM\Relation;
use Arcano\ORM\Schema;
use Arcano\ORM\Service\EntityFactoryInterface;
use Arcano\ORM\Tests\Fixtures\Comment;
use Arcano\ORM\Tests\Fixtures\User;
use Arcano\ORM\Tests\Functional\Driver\Common\BaseTest;
use Arcano\ORM\Tests\Traits\TableTrait;

/**
 * Has-many loaded for a whole parent table reads the related table without the IN
 * list and keeps only the rows of loaded parents.
 */
abstract class EagerWholeTableTest extends BaseTest
{
    use TableTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->makeTable('user', ['id' => 'primary', 'email' => 'string', 'balance' => 'float', 'user_code' => 'integer,null']);
        $this->makeIndex('user', ['email'], true);
        $this->makeTable('comment', ['id' => 'primary', 'user_id' => 'integer,null', 'message' => 'string']);

        $this->getDatabase()->table('user')->insertMultiple(
            ['email', 'balance', 'user_code'],
            [['hello@world.com', 100, 1], ['another@world.com', 200, null]],
        );
        $this->getDatabase()->table('comment')->insertMultiple(
            ['user_id', 'message'],
            [[1, 'msg 1'], [2, 'msg 2'], [null, 'no user'], [99, 'orphan'], [1, 'msg 3']],
        );

        $this->orm = $this->withSchema(new Schema([
            User::class => [
                Schema::ROLE => 'user',
                Schema::MAPPER => Mapper::class,
                Schema::DATABASE => 'default',
                Schema::TABLE => 'user',
                Schema::PRIMARY_KEY => 'id',
                Schema::FIND_BY_KEYS => ['email'],
                Schema::COLUMNS => ['id', 'email', 'balance', 'user_code'],
                Schema::TYPECAST => ['id' => 'int', 'balance' => 'float', 'user_code' => 'int'],
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
                    'favorites' => [
                        Relation::TYPE => Relation::HAS_MANY,
                        Relation::TARGET => Comment::class,
                        Relation::SCHEMA => [
                            Relation::CASCADE => true,
                            Relation::INNER_KEY => 'user_code',
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

    public function testWholeTableKeepsOnlyLoadedParents(): void
    {
        $this->captureReadQueries();
        $users = $this->orm->getRepository(User::class)->select()->load('comments')->orderBy('id')->fetchAll();
        $this->assertNumReads(2);

        $this->assertSame(['msg 1', 'msg 3'], $this->messages($users[0]));
        $this->assertSame(['msg 2'], $this->messages($users[1]));
        $heap = $this->orm->getHeap();
        $this->assertNull($heap->find('comment', ['id' => 3]));
        $this->assertNull($heap->find('comment', ['id' => 4]));
        $this->assertNotNull($heap->find('comment', ['id' => 5]));
    }

    public function testRelationConditionsApply(): void
    {
        $users = $this->orm->getRepository(User::class)->select()
            ->load('comments', ['where' => ['message' => ['!=' => 'msg 2']]])
            ->orderBy('id')->fetchAll();

        $this->assertSame(['msg 1', 'msg 3'], $this->messages($users[0]));
        $this->assertSame([], $this->messages($users[1]));
        $this->assertNull($this->orm->getHeap()->find('comment', ['id' => 2]));
    }

    public function testFilteredParentsUseTheKeys(): void
    {
        $users = $this->orm->getRepository(User::class)->select()->load('comments')->where('id', 2)->fetchAll();

        $this->assertCount(1, $users);
        $this->assertSame(['msg 2'], $this->messages($users[0]));
        $this->assertNull($this->orm->getHeap()->find('comment', ['id' => 1]));
    }

    public function testParentsAlreadyInTheHeapGetTheirComments(): void
    {
        $first = $this->orm->getRepository(User::class)->findByPK(1);
        $users = $this->orm->getRepository(User::class)->select()->load('comments')->orderBy('id')->fetchAll();

        $this->assertSame($first, $users[0]);
        $this->assertSame(['msg 1', 'msg 3'], $this->messages($first));
    }

    public function testUndeclaredColumnIsHydratedAsByTheParser(): void
    {
        $fast = $this->orm->getRepository(Comment::class)->select()->where('id', 1)->fetchAll()[0];
        $this->orm->getHeap()->clean();
        $parsed = \iterator_to_array($this->orm->getRepository(Comment::class)->select()->where('id', 1)->getIterator())[0];

        $this->assertNotSame($fast, $parsed);
        $this->assertSame($this->fields($parsed), $this->fields($fast));
    }

    public function testEveryRelationLoadedMatchesTheParser(): void
    {
        $select = fn () => $this->orm->getRepository(User::class)->select()
            ->load('comments')->load('favorites')->orderBy('id');
        $fast = $select()->fetchAll();
        $this->orm->getHeap()->clean();
        $parsed = \iterator_to_array($select()->getIterator());

        $this->assertSame(['msg 1', 'msg 3'], $this->messages($fast[0]));
        $this->assertSame(['msg 2'], $this->messages($fast[1]));
        foreach ([0, 1] as $i) {
            $this->assertSame($this->messages($parsed[$i]), $this->messages($fast[$i]));
            $this->assertSame($this->texts($parsed[$i]->favorites), $this->texts($fast[$i]->favorites));
        }
        // The user without a code reads its favorites lazily, as the parser leaves them.
        $this->assertSame([], $this->texts($fast[1]->favorites));
    }

    public function testEveryRelationLoadedKeepsChangesTracked(): void
    {
        $user = $this->orm->getRepository(User::class)->select()
            ->load('comments')->load('favorites')->orderBy('id')->fetchAll()[1];
        $comment = new Comment();
        $comment->message = 'new';
        $user->comments[] = $comment;
        (new EntityManager($this->orm))->persist($user)->run();

        $this->orm->getHeap()->clean();
        $user = $this->orm->getRepository(User::class)->findByPK(2);
        $this->assertSame(['msg 2', 'new'], $this->messages($user));
    }

    public function testRowMakerKeepsOneEntityPerKey(): void
    {
        $factory = $this->orm->getService(EntityFactoryInterface::class);
        $maker = $factory->rowMaker('comment', ['id', 'user_id', 'message']);
        $this->assertNotNull($maker);

        $made = $maker([[1, 1, 'a'], [2, 1, 'b'], [1, 1, 'a'], [3, null, 'c'], [2, 1, 'b']]);
        $this->assertSame(['a', 'b', 'c'], \array_map(static fn (Comment $c) => $c->message, $made));

        // Entities already in the heap are returned once too.
        $again = $maker([[4, 2, 'd'], [1, 1, 'x'], [4, 2, 'd'], [1, 1, 'x']]);
        $this->assertCount(2, $again);
        $this->assertSame($made[0], $again[1]);
    }

    private function texts(?iterable $comments): array
    {
        $messages = [];
        foreach ($comments ?? [] as $comment) {
            $messages[] = $comment->message;
        }
        \sort($messages);

        return $messages;
    }

    private function fields(object $entity): array
    {
        $fields = [];
        foreach ((array) $entity as $name => $value) {
            \str_contains($name, '__') or $fields[$name] = \is_object($value) ? $value::class : $value;
        }

        return $fields;
    }

    private function messages(User $user): array
    {
        $messages = [];
        foreach ($user->comments as $comment) {
            $messages[] = $comment->message;
        }
        \sort($messages);

        return $messages;
    }
}

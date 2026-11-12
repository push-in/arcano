<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Arcano\Database\Config\DatabaseConfig;
use Arcano\Database\Config\SQLite\MemoryConnectionConfig;
use Arcano\Database\Config\SQLiteDriverConfig;
use Arcano\Database\DatabaseManager;
use Arcano\ORM\EntityManager;
use Arcano\ORM\Factory;
use Arcano\ORM\Heap\Heap;
use Arcano\ORM\Mapper\Mapper;
use Arcano\ORM\ORM;
use Arcano\ORM\Relation;
use Arcano\ORM\Schema;
use Arcano\ORM\SchemaInterface as S;

class User
{
    public ?int $id = null;
    public string $name = '';
    public string $email = '';
    public bool $active = true;
    public iterable $posts = [];
}

class Post
{
    public ?int $id = null;
    public int $user_id = 0;
    public string $title = '';
    public ?User $user = null;
}

const USERS = 2000;
const POSTS_PER_USER = 5;

$dbal = new DatabaseManager(new DatabaseConfig([
    'default' => 'default',
    'databases' => ['default' => ['connection' => 'sqlite']],
    'connections' => ['sqlite' => new SQLiteDriverConfig(connection: new MemoryConnectionConfig(), queryCache: true)],
]));
$db = $dbal->database();
$db->execute('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, email TEXT, active INTEGER)');
$db->execute('CREATE TABLE posts (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, title TEXT)');
$db->execute('CREATE INDEX posts_user ON posts (user_id)');

$schema = new Schema([
    'user' => [
        S::ENTITY => User::class,
        S::MAPPER => Mapper::class,
        S::DATABASE => 'default',
        S::TABLE => 'users',
        S::PRIMARY_KEY => 'id',
        S::COLUMNS => ['id' => 'id', 'name' => 'name', 'email' => 'email', 'active' => 'active'],
        S::TYPECAST => ['id' => 'int', 'active' => 'bool'],
        S::SCHEMA => [],
        S::RELATIONS => [
            'posts' => [
                Relation::TYPE => Relation::HAS_MANY,
                Relation::TARGET => 'post',
                Relation::LOAD => Relation::LOAD_PROMISE,
                Relation::SCHEMA => [
                    Relation::CASCADE => true,
                    Relation::INNER_KEY => 'id',
                    Relation::OUTER_KEY => 'user_id',
                    Relation::WHERE => [],
                    Relation::ORDER_BY => [],
                ],
            ],
        ],
    ],
    'post' => [
        S::ENTITY => Post::class,
        S::MAPPER => Mapper::class,
        S::DATABASE => 'default',
        S::TABLE => 'posts',
        S::PRIMARY_KEY => 'id',
        S::COLUMNS => ['id' => 'id', 'user_id' => 'user_id', 'title' => 'title'],
        S::TYPECAST => ['id' => 'int', 'user_id' => 'int'],
        S::SCHEMA => [],
        S::RELATIONS => [],
    ],
]);

$orm = new ORM(new Factory($dbal), $schema);
$pdo = (fn () => $this->getPDO())->call($db->getDriver());

function bench(string $name, callable $fn, int $repeat = 5): float
{
    $fn(); // warm
    $times = [];
    for ($i = 0; $i < $repeat; $i++) {
        gc_collect_cycles();
        $t = hrtime(true);
        $fn();
        $times[] = (hrtime(true) - $t) / 1e6;
    }
    sort($times);
    return $times[intdiv(count($times), 2)];
}

$results = [];

// 1. insert USERS users with POSTS_PER_USER posts each, in one unit of work
$results['insert'] = [
    bench('insert', function () use ($orm, $db) {
        $db->execute('DELETE FROM posts');
        $db->execute('DELETE FROM users');
        $orm->getHeap()->clean();
        $em = new EntityManager($orm);
        for ($i = 0; $i < USERS; $i++) {
            $u = new User();
            $u->name = "User $i";
            $u->email = "u$i@example.com";
            $posts = [];
            for ($p = 0; $p < POSTS_PER_USER; $p++) {
                $post = new Post();
                $post->title = "Post $p of $i";
                $posts[] = $post;
            }
            $u->posts = $posts;
            $em->persist($u);
        }
        $em->run();
    }, 3),
    bench('insert-pdo', function () use ($pdo) {
        $pdo->exec('DELETE FROM posts');
        $pdo->exec('DELETE FROM users');
        $pdo->beginTransaction();
        $su = $pdo->prepare('INSERT INTO users (name, email, active) VALUES (?, ?, ?)');
        $sp = $pdo->prepare('INSERT INTO posts (user_id, title) VALUES (?, ?)');
        for ($i = 0; $i < USERS; $i++) {
            $su->execute(["User $i", "u$i@example.com", 1]);
            $id = (int) $pdo->lastInsertId();
            for ($p = 0; $p < POSTS_PER_USER; $p++) {
                $sp->execute([$id, "Post $p of $i"]);
            }
        }
        $pdo->commit();
    }, 3),
];

// fresh, stable data for read scenarios
$orm->getHeap()->clean();
$em = new EntityManager($orm);
$pdo->exec('DELETE FROM posts');
$pdo->exec('DELETE FROM users');
for ($i = 0; $i < USERS; $i++) {
    $u = new User();
    $u->name = "User $i";
    $u->email = "u$i@example.com";
    $u->active = $i % 3 !== 0;
    $posts = [];
    for ($p = 0; $p < POSTS_PER_USER; $p++) {
        $post = new Post();
        $post->title = "Post $p of $i";
        $posts[] = $post;
    }
    $u->posts = $posts;
    $em->persist($u);
}
$em->run();

$ids = array_map('intval', $pdo->query('SELECT id FROM users ORDER BY id')->fetchAll(PDO::FETCH_COLUMN));
$firstId = $ids[0];

// 2. select all users, hydrate
$results['select_all'] = [
    bench('select', function () use ($orm) {
        $orm->getHeap()->clean();
        $all = $orm->getRepository('user')->select()->fetchAll();
        assert(count($all) === USERS);
    }),
    bench('select-pdo', function () use ($pdo) {
        $out = [];
        foreach ($pdo->query('SELECT id, name, email, active FROM users')->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $u = new User();
            $u->id = (int) $r['id'];
            $u->name = $r['name'];
            $u->email = $r['email'];
            $u->active = (bool) $r['active'];
            $out[] = $u;
        }
    }),
];

// 3. find by primary key, empty heap each time
$results['find_pk'] = [
    bench('find', function () use ($orm, $ids) {
        $repo = $orm->getRepository('user');
        foreach ($ids as $i) {
            $orm->getHeap()->clean();
            assert($repo->findByPK($i) !== null);
        }
    }),
    bench('find-pdo', function () use ($pdo, $ids) {
        $st = $pdo->prepare('SELECT id, name, email, active FROM users WHERE id = ?');
        foreach ($ids as $i) {
            $st->execute([$i]);
            $r = $st->fetch(PDO::FETCH_ASSOC);
            $u = new User();
            $u->id = (int) $r['id'];
            $u->name = $r['name'];
            $u->email = $r['email'];
            $u->active = (bool) $r['active'];
        }
    }),
];

// 4. eager load has-many
$results['eager_has_many'] = [
    bench('eager', function () use ($orm) {
        $orm->getHeap()->clean();
        $all = $orm->getRepository('user')->select()->load('posts')->fetchAll();
        assert(count($all) === USERS);
    }),
    bench('eager-pdo', function () use ($pdo) {
        $users = [];
        foreach ($pdo->query('SELECT id, name, email, active FROM users')->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $u = new User();
            $u->id = (int) $r['id'];
            $u->name = $r['name'];
            $u->email = $r['email'];
            $u->active = (bool) $r['active'];
            $u->posts = [];
            $users[$u->id] = $u;
        }
        foreach ($pdo->query('SELECT id, user_id, title FROM posts')->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $p = new Post();
            $p->id = (int) $r['id'];
            $p->user_id = (int) $r['user_id'];
            $p->title = $r['title'];
            $users[$p->user_id]->posts[] = $p;
        }
    }),
];

// 5. update every user in one unit of work
$results['update'] = [
    bench('update', function () use ($orm) {
        $orm->getHeap()->clean();
        $all = $orm->getRepository('user')->select()->fetchAll();
        $em = new EntityManager($orm);
        foreach ($all as $u) {
            $u->name .= '!';
            $em->persist($u);
        }
        $em->run();
    }, 3),
    bench('update-pdo', function () use ($pdo) {
        $rows = $pdo->query('SELECT id, name FROM users')->fetchAll(PDO::FETCH_ASSOC);
        $pdo->beginTransaction();
        $st = $pdo->prepare('UPDATE users SET name = ? WHERE id = ?');
        foreach ($rows as $r) {
            $st->execute([$r['name'] . '!', $r['id']]);
        }
        $pdo->commit();
    }, 3),
];

// 6. many small filtered queries (query building and compilation cost)
$results['small_queries'] = [
    bench('small', function () use ($orm, $firstId) {
        $repo = $orm->getRepository('user');
        for ($i = 0; $i < USERS; $i++) {
            $orm->getHeap()->clean();
            $repo->select()->where('active', true)->where('id', '>', $firstId + $i)->orderBy('id')->limit(5)->fetchAll();
        }
    }),
    bench('small-pdo', function () use ($pdo, $firstId) {
        $st = $pdo->prepare('SELECT id, name, email, active FROM users WHERE active = ? AND id > ? ORDER BY id LIMIT 5');
        for ($i = 0; $i < USERS; $i++) {
            $st->execute([1, $firstId + $i]);
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
                $u = new User();
                $u->id = (int) $r['id'];
                $u->name = $r['name'];
                $u->email = $r['email'];
                $u->active = (bool) $r['active'];
            }
        }
    }),
];

printf("%-16s %10s %10s %10s\n", 'scenario', 'arcano ms', 'pdo ms', 'overhead');
foreach ($results as $name => [$orm_ms, $pdo_ms]) {
    printf("%-16s %10.1f %10.1f %9.1fx\n", $name, $orm_ms, $pdo_ms, $orm_ms / $pdo_ms);
}
file_put_contents(__DIR__ . '/results.json', json_encode($results, JSON_PRETTY_PRINT));

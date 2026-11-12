<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Mapper;

use Arcano\ORM\Heap\Heap;
use Arcano\ORM\Schema;
use Arcano\ORM\SchemaInterface;
use Arcano\ORM\Select;
use Arcano\ORM\Tests\Functional\Driver\Common\BaseTest;
use Arcano\ORM\Tests\Fixtures\TimestampedMapper;
use Arcano\ORM\Tests\Fixtures\User;
use Arcano\ORM\Tests\Traits\TableTrait;
use Arcano\ORM\Transaction;

abstract class AutoTimestampsTest extends BaseTest
{
    use TableTrait;

    public function testCreate(): void
    {
        $u = new User();
        $u->email = 'test@email.com';
        $u->balance = 199;

        $this->save($u);

        $s = new Select($this->orm->withHeap(new Heap()), User::class);
        $data = $s->fetchData();

        $this->assertNotNull($data[0]['created_at']);
        $this->assertNotNull($data[0]['updated_at']);
    }

    public function testNoWrites(): void
    {
        $u = new User();
        $u->email = 'test@email.com';
        $u->balance = 199;

        $this->save($u);

        $orm = $this->orm->withHeap(new Heap());
        $s = new Select($orm, User::class);
        $u = $s->fetchOne();

        $this->captureWriteQueries();
        (new Transaction($orm))->persist($u)->run();
        $this->assertNumWrites(0);
    }

    public function testUpdate(): void
    {
        $u = new User();
        $u->email = 'test@email.com';
        $u->balance = 199;

        $this->save($u);

        $this->orm = $this->orm->withHeap(new Heap());
        $s = new Select($this->orm, User::class);

        $u = $s->fetchOne();

        $u->balance = 200;

        $this->captureWriteQueries();
        $this->save($u);
        $this->assertNumWrites(1);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->makeTable(
            'user',
            [
                'id' => 'primary',
                'email' => 'string',
                'balance' => 'float',
                'created_at' => 'datetime',
                'updated_at' => 'datetime',
            ],
        );

        $this->orm = $this->withSchema(
            new Schema(
                [
                    User::class => [
                        SchemaInterface::ROLE => 'user',
                        SchemaInterface::MAPPER => TimestampedMapper::class,
                        SchemaInterface::DATABASE => 'default',
                        SchemaInterface::TABLE => 'user',
                        SchemaInterface::PRIMARY_KEY => 'id',
                        SchemaInterface::COLUMNS => ['id', 'email', 'balance', 'created_at', 'updated_at'],
                        SchemaInterface::TYPECAST => [
                            'id' => 'int',
                            'balance' => 'float',
                            'created_at' => 'datetime',
                            'updated_at' => 'datetime',
                        ],
                        SchemaInterface::SCHEMA => [],
                        SchemaInterface::RELATIONS => [],
                    ],
                ],
            ),
        );
    }
}

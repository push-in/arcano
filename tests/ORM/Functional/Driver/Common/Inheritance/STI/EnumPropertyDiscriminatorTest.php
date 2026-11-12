<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Inheritance\STI;

use Arcano\ORM\Heap\Heap;
use Arcano\ORM\Mapper\Mapper;
use Arcano\ORM\Schema;
use Arcano\ORM\SchemaInterface;
use Arcano\ORM\Select;
use Arcano\ORM\Tests\Functional\Driver\Common\Inheritance\Fixture\BossWithKind;
use Arcano\ORM\Tests\Functional\Driver\Common\Inheritance\Fixture\EmployeeType;
use Arcano\ORM\Tests\Functional\Driver\Common\Inheritance\Fixture\WorkerWithKind;

/**
 * STI scenario where the entity exposes the discriminator as a BackedEnum-typed property
 * (so `extractData` returns an enum case in the discriminator slot). Exercises the
 * enum -> scalar conversion performed by the BackedEnum uncaster in {@see \Arcano\ORM\Parser\Typecast::uncast()}.
 */
abstract class EnumPropertyDiscriminatorTest extends StiBaseTest
{
    protected const WORKER_ROLE = 'worker';
    protected const BOSS_ROLE = 'boss';

    public function testInsertChildWritesScalarDespiteEnumOnProperty(): void
    {
        $boss = new BossWithKind();
        $boss->type = EmployeeType::Manager;
        $boss->name = 'Boss';
        $boss->email = 'boss@corp.example';
        $boss->age = 50;
        $boss->level = 10;

        $this->save($boss);

        $row = $this->getDatabase()
            ->table('worker_table')
            ->select()
            ->where('id', $boss->id)
            ->fetchAll()[0];

        $this->assertSame('manager', $row['type']);
    }

    public function testInsertBaseWithEnumPropertyWritesScalar(): void
    {
        // Base entity does NOT match any child in CHILDREN -> getDiscriminatorValues() returns [].
        // The enum on $entity->type is converted to a scalar by the BackedEnum uncaster in Typecast::uncast().
        $worker = new WorkerWithKind();
        $worker->type = EmployeeType::Employee;
        $worker->name = 'Plain Worker';
        $worker->email = 'plain@corp.example';
        $worker->age = 30;

        $this->save($worker);

        $row = $this->getDatabase()
            ->table('worker_table')
            ->select()
            ->where('id', $worker->id)
            ->fetchAll()[0];

        $this->assertSame('employee', $row['type']);
    }

    public function testRoundTripPreservesEnumOnEntity(): void
    {
        $boss = new BossWithKind();
        $boss->type = EmployeeType::Manager;
        $boss->name = 'Boss';
        $boss->email = 'boss@corp.example';
        $boss->age = 50;
        $boss->level = 10;
        $this->save($boss);

        /** @var BossWithKind $loaded */
        $loaded = (new Select($this->orm->withHeap(new Heap()), WorkerWithKind::class))
            ->wherePK($boss->id)
            ->fetchOne();

        $this->assertInstanceOf(BossWithKind::class, $loaded);
        $this->assertSame(EmployeeType::Manager, $loaded->type);
    }

    public function testNoExtraWritesAfterRoundTrip(): void
    {
        $boss = new BossWithKind();
        $boss->type = EmployeeType::Manager;
        $boss->name = 'Boss';
        $boss->email = 'boss@corp.example';
        $boss->age = 50;
        $boss->level = 10;
        $this->save($boss);

        $this->orm = $this->orm->withHeap(new Heap());

        /** @var BossWithKind $loaded */
        $loaded = (new Select($this->orm, WorkerWithKind::class))
            ->wherePK($boss->id)
            ->fetchOne();

        $this->captureWriteQueries();
        $this->save($loaded);
        $this->assertNumWrites(0);
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->makeTable('worker_table', [
            'type' => 'string,nullable',
            'id' => 'primary',
            'name' => 'string',
            'email' => 'string',
            'age' => 'int',
            'level' => 'int,nullable',
        ]);

        $this->orm = $this->withSchema(new Schema($this->getSchemaArray()));
    }

    protected function getSchemaArray(): array
    {
        return [
            self::WORKER_ROLE => [
                SchemaInterface::ENTITY => WorkerWithKind::class,
                SchemaInterface::CHILDREN => [
                    'manager' => BossWithKind::class,
                ],
                SchemaInterface::MAPPER => Mapper::class,
                SchemaInterface::DATABASE => 'default',
                SchemaInterface::TABLE => 'worker_table',
                SchemaInterface::PRIMARY_KEY => 'id',
                SchemaInterface::DISCRIMINATOR => 'type',
                SchemaInterface::COLUMNS => ['id', 'type', 'name', 'email', 'age', 'level'],
                SchemaInterface::TYPECAST => [
                    'id' => 'int',
                    'age' => 'int',
                    'level' => 'int',
                    'type' => EmployeeType::class,
                ],
                SchemaInterface::SCHEMA => [],
                SchemaInterface::RELATIONS => [],
            ],
            self::BOSS_ROLE => [
                SchemaInterface::ENTITY => BossWithKind::class,
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Unit;

use Arcano\Database\Config\DatabaseConfig;
use Arcano\Database\DatabaseManager;
use Arcano\ORM\Config\RelationConfig;
use Arcano\ORM\Factory;
use Arcano\ORM\Heap\Heap;
use Arcano\ORM\ORM;
use Arcano\ORM\Schema;
use Arcano\ORM\Tests\Util\SimpleFactory;
use PHPUnit\Framework\TestCase;

final class OrmTest extends TestCase
{
    public function testPrepareServicesTwice(): void
    {
        $orm = $this->createOrm();
        $orm->prepareServices();
        $orm->prepareServices();
        $this->assertTrue(true, 'There aren\'t any errors.');
    }

    public function testORMClone(): void
    {
        $orm = $this->createOrm();
        $new = $orm->withFactory($orm->getFactory());
        $this->assertNotSame($orm, $new);
    }

    public function testORMCloneWithSchema(): void
    {
        $orm = $this->createOrm();
        $new = $orm->with(new Schema([]));

        $this->assertNotSame($new, $orm);
        $this->assertNotSame($new->getSchema(), $orm->getSchema());
    }

    public function testORMCloneWithFactory(): void
    {
        $orm = $this->createOrm();
        $new = $orm->with(factory: new Factory(new DatabaseManager(new DatabaseConfig([]))));

        $this->assertNotSame($orm, $new);
        $this->assertNotSame($new->getFactory(), $orm->getFactory());
    }

    public function testORMCloneWithHeap(): void
    {
        $orm = $this->createOrm();
        $new = $orm->with(heap: new Heap());

        $this->assertNotSame($new, $orm);
        $this->assertNotSame($new->getHeap(), $orm->getHeap());
    }

    private function createOrm(): ORM
    {
        return new ORM(
            factory: new Factory(
                new DatabaseManager(new DatabaseConfig([])),
                RelationConfig::getDefault(),
                new SimpleFactory(),
            ),
            schema: new Schema([]),
        );
    }
}

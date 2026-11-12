<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Unit;

use Arcano\Database\DatabaseProviderInterface;
use Arcano\ORM\Collection\ArrayCollectionFactory;
use Arcano\ORM\Collection\CollectionFactoryInterface;
use Arcano\ORM\Factory;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testArrayAliasKeepsArraysWithAnotherDefault(): void
    {
        $default = m::mock(CollectionFactoryInterface::class);
        $factory = new Factory(m::mock(DatabaseProviderInterface::class), defaultCollectionFactory: $default);

        $this->assertSame($default, $factory->collection());
        $this->assertInstanceOf(ArrayCollectionFactory::class, $factory->collection('array'));
    }
}

<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Unit\Collection;

use Arcano\ORM\Collection\ArrayCollectionFactory;
use Arcano\ORM\Collection\CollectionFactoryInterface;

class ArrayCollectionFactoryTest extends BaseTest
{
    public function testGetInterface(): void
    {
        $this->assertNull($this->getFactory()->getInterface());
    }

    /**
     * @dataProvider collectionDataProvider
     * @param mixed $data
     */
    public function testCollectShouldReturnArray($data): void
    {
        $this->assertSame([
            'foo' => 'bar',
            'baz' => 'bar',
        ], $this->getFactory()->collect($data));
    }

    protected function getFactory(): CollectionFactoryInterface
    {
        return new ArrayCollectionFactory();
    }
}

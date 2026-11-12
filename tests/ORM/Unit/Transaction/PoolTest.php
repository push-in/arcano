<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Unit\Transaction;

use Arcano\ORM\ORMInterface;
use Arcano\ORM\Transaction\Pool;
use Arcano\ORM\Transaction\Tuple;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class PoolTest extends TestCase
{
    public function testDoubleAttachSameEntityWithNullNode(): void
    {
        $orm = m::mock(ORMInterface::class);
        $pool = new Pool($orm);
        $entity = new \stdClass();

        $pool->attach($entity, Tuple::TASK_DELETE, false);
        $pool->attach($entity, Tuple::TASK_DELETE, false);

        // There is no exception here
        $this->assertTrue(true);
    }
}

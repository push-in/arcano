<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Unit\Command;

use Arcano\ORM\Command\Database\Delete;
use Arcano\ORM\Exception\CommandException;
use Arcano\ORM\Heap\Node;
use Arcano\ORM\Heap\State;
use Arcano\ORM\MapperInterface;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Arcano\Database\DatabaseInterface;

class DeleteCommandTest extends TestCase
{
    private m\LegacyMockInterface|m\MockInterface|MapperInterface $mapper;

    public function testNoScope(): void
    {
        $this->expectException(CommandException::class);

        $this->mapper = m::mock(MapperInterface::class);
        $state = new State(Node::SCHEDULED_DELETE, []);
        $cmd = new Delete(
            m::mock(DatabaseInterface::class),
            'table',
            $state,
            $this->mapper,
        );

        $cmd->execute();
    }
}

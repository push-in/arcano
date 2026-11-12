<?php

// phpcs:ignoreFile
declare(strict_types=1);

namespace Arcano\ORM\Tests\Fixtures;

use Arcano\ORM\Command\CommandInterface;
use Arcano\ORM\Heap\Node;
use Arcano\ORM\Heap\State;
use Arcano\ORM\Mapper\Mapper;

class SoftDeletedMapper extends Mapper
{
    public function queueDelete($entity, Node $node, State $state): CommandInterface
    {
        $state->setData(['deleted_at' => new \DateTimeImmutable()]);

        $cmd = $this->queueUpdate($entity, $node, $state);

        $state->setStatus(Node::SCHEDULED_DELETE);

        return $cmd;
    }
}

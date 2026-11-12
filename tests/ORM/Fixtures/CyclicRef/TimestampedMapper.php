<?php

// phpcs:ignoreFile
declare(strict_types=1);

namespace Arcano\ORM\Tests\Fixtures\CyclicRef;

use Arcano\ORM\Command\CommandInterface;
use Arcano\ORM\Command\Database\Insert;
use Arcano\ORM\Command\Database\Update;
use Arcano\ORM\Heap\Node;
use Arcano\ORM\Heap\State;
use Arcano\ORM\Mapper\Mapper;

class TimestampedMapper extends Mapper
{
    public function queueCreate($entity, Node $node, State $state): CommandInterface
    {
        /** @var Insert $cmd */
        $cmd = parent::queueCreate($entity, $node, $state);

        $dt = new \DateTimeImmutable();
        $state->register('created_at', $dt);
        $state->register('updated_at', $dt);

        return $cmd;
    }

    public function queueUpdate($entity, Node $node, State $state): CommandInterface
    {
        /** @var Update $cmd */
        $cmd = parent::queueUpdate($entity, $node, $state);

        $cmd->registerAppendix('updated_at', new \DateTimeImmutable());

        return $cmd;
    }
}

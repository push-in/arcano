<?php

// phpcs:ignoreFile
declare(strict_types=1);

namespace Arcano\ORM\Tests\Fixtures;

use Arcano\ORM\Command\CommandInterface;
use Arcano\ORM\Heap\Node;
use Arcano\ORM\Heap\State;
use Arcano\ORM\Mapper\Mapper;
use Arcano\Database\Injection\Fragment;

class SequenceDefaultValueMapper extends Mapper
{
    public function queueCreate($entity, Node $node, State $state): CommandInterface
    {
        $command = parent::queueCreate($entity, $node, $state);

        $state->register('user_code', new Fragment('nextval(\'user_code_seq\')'));

        return $command;
    }
}

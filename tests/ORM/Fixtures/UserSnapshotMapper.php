<?php

// phpcs:ignoreFile
declare(strict_types=1);

namespace Arcano\ORM\Tests\Fixtures;

use Arcano\ORM\Command\Special\Sequence;
use Arcano\ORM\Command\Special\WrappedStoreCommand;
use Arcano\ORM\Command\CommandInterface;
use Arcano\ORM\Command\StoreCommandInterface;
use Arcano\ORM\Heap\Node;
use Arcano\ORM\Heap\State;
use Arcano\ORM\Mapper\Mapper;

class UserSnapshotMapper extends Mapper
{
    public function queueCreate($entity, Node $node, State $state): CommandInterface
    {
        $cc = parent::queueCreate($entity, $node, $state);

        $cs = new Sequence();
        $cs->addCommand($cc);
        $cs->addCommand($this->snap($state, 'create'));

        return $cs;
    }

    public function queueUpdate($entity, Node $node, State $state): CommandInterface
    {
        $cc = parent::queueUpdate($entity, $node, $state);

        $cs = new Sequence();
        $cs->addCommand($cc);
        $cs->addCommand($this->snap($state, 'update'));

        return $cs;
    }

    protected function snap(State $origin, string $action): StoreCommandInterface
    {
        $data = $origin->getData();
        unset($data['id']);
        $state = new State(Node::SCHEDULED_INSERT, $data + [
            'at' => new \DateTimeImmutable(),
            'action' => $action,
        ]);

        return WrappedStoreCommand::createInsert(
            $this->source->getDatabase(),
            'user_snapshots',
            $state,
            null,
        )->withBeforeExecution(static function (StoreCommandInterface $command) use ($origin, $state): void {
            $state->register('user_id', $origin->getData()['id']);
        });
    }
}

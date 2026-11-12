<?php

declare(strict_types=1);

namespace Arcano\Migrations\Operation\Table;

use Arcano\Database\Driver\HandlerInterface;
use Arcano\Migrations\CapsuleInterface;
use Arcano\Migrations\Exception\Operation\TableException;
use Arcano\Migrations\Operation\AbstractOperation;

final class Update extends AbstractOperation
{
    public function execute(CapsuleInterface $capsule): void
    {
        $schema = $capsule->getSchema($this->getTable());
        $database = $this->database ?? '[default]';

        if (!$schema->exists()) {
            throw new TableException(
                "Unable to update table '{$database}'.'{$this->getTable()}', no table exists",
            );
        }

        $schema->save(HandlerInterface::DO_ALL);
    }
}

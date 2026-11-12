<?php

declare(strict_types=1);

namespace Arcano\Migrations\Operation\Table;

use Arcano\Database\Driver\HandlerInterface;
use Arcano\Migrations\CapsuleInterface;
use Arcano\Migrations\Exception\Operation\TableException;
use Arcano\Migrations\Operation\AbstractOperation;

final class Create extends AbstractOperation
{
    public function execute(CapsuleInterface $capsule): void
    {
        $schema = $capsule->getSchema($this->getTable());
        $database = $this->database ?? '[default]';

        if ($schema->exists()) {
            throw new TableException(
                "Unable to create table '{$database}'.'{$this->getTable()}', table already exists",
            );
        }

        if (empty($schema->getColumns())) {
            throw new TableException(
                "Unable to create table '{$database}'.'{$this->getTable()}', no columns were added",
            );
        }

        $schema->save(HandlerInterface::DO_ALL);
    }
}

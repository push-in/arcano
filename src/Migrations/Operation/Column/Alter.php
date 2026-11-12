<?php

declare(strict_types=1);

namespace Arcano\Migrations\Operation\Column;

use Arcano\Migrations\CapsuleInterface;
use Arcano\Migrations\Exception\Operation\ColumnException;

final class Alter extends Column
{
    public function execute(CapsuleInterface $capsule): void
    {
        $schema = $capsule->getSchema($this->getTable());

        if (!$schema->hasColumn($this->name)) {
            throw new ColumnException(
                "Unable to alter column '{$schema->getName()}'.'{$this->name}', column does not exists",
            );
        }

        //Declaring column change
        $this->declareColumn($schema);
    }
}

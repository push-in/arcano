<?php

declare(strict_types=1);

namespace Arcano\Migrations\Operation\Column;

use Arcano\Migrations\CapsuleInterface;
use Arcano\Migrations\Exception\Operation\ColumnException;
use Arcano\Migrations\Operation\AbstractOperation;

final class Rename extends AbstractOperation
{
    public function __construct(string $table, private string $name, private string $newName)
    {
        parent::__construct($table);
    }

    public function execute(CapsuleInterface $capsule): void
    {
        $schema = $capsule->getSchema($this->getTable());

        if (!$schema->hasColumn($this->name)) {
            throw new ColumnException(
                "Unable to rename column '{$schema->getName()}'.'{$this->name}', column does not exists",
            );
        }

        if ($schema->hasColumn($this->newName)) {
            throw new ColumnException(
                \sprintf(
                    "Unable to rename column '%s'.'%s', column '%s' already exists",
                    $schema->getName(),
                    $this->name,
                    $this->newName,
                ),
            );
        }

        //Declaring column
        $schema->renameColumn($this->name, $this->newName);
    }
}

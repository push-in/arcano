<?php

declare(strict_types=1);

namespace Arcano\Migrations\Operation;

use Arcano\Migrations\OperationInterface;

abstract class AbstractOperation implements OperationInterface
{
    public function __construct(protected string $table) {}

    public function getTable(): string
    {
        return $this->table;
    }
}

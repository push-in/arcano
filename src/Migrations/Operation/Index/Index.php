<?php

declare(strict_types=1);

namespace Arcano\Migrations\Operation\Index;

use Arcano\Migrations\Operation\AbstractOperation;

abstract class Index extends AbstractOperation
{
    public function __construct(string $table, protected array $columns)
    {
        parent::__construct($table);
    }
}

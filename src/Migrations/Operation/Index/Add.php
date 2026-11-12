<?php

declare(strict_types=1);

namespace Arcano\Migrations\Operation\Index;

use Arcano\Migrations\CapsuleInterface;
use Arcano\Migrations\Exception\Operation\IndexException;
use Arcano\Migrations\Operation\Traits\OptionsTrait;

final class Add extends Index
{
    use OptionsTrait;

    public function __construct(string $table, array $columns, array $options = [])
    {
        $this->options = $options;
        parent::__construct($table, $columns);
    }

    public function execute(CapsuleInterface $capsule): void
    {
        $schema = $capsule->getSchema($this->getTable());

        if ($schema->hasIndex($this->columns)) {
            $columns = \implode(',', $this->columns);
            throw new IndexException(
                "Unable to create index '{$schema->getName()}'.({$columns}), index already exists",
            );
        }

        $schema->index($this->columns)->unique(
            $this->getOption('unique', false),
        );

        if ($this->hasOption('name')) {
            $schema->index($this->columns)->setName($this->getOption('name'));
        }
    }
}

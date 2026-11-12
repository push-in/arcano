<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Fixtures;

use Arcano\Database\DatabaseInterface;
use Arcano\ORM\Command\CommandInterface;

class TestCommand implements CommandInterface
{
    private DatabaseInterface $database;
    private bool $executed = false;

    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    public function isReady(): bool
    {
        return true;
    }

    public function isExecuted(): bool
    {
        return $this->executed;
    }

    public function execute(): void
    {
        $this->executed = true;
    }

    public function getDatabase(): ?DatabaseInterface
    {
        return $this->database;
    }
}

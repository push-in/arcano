<?php

// phpcs:ignoreFile
declare(strict_types=1);

namespace Arcano\ORM\Tests\Fixtures;

use Arcano\ORM\Select\ScopeInterface;
use Arcano\ORM\Select\SourceInterface;
use Arcano\Database\DatabaseInterface;

class DifferentSource implements SourceInterface
{
    public function getDatabase(): DatabaseInterface
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function getTable(): string
    {
        throw new \RuntimeException('Not implemented.');
    }

    public function withScope(?ScopeInterface $scope): SourceInterface
    {
        return $this;
    }

    public function getScope(): ?ScopeInterface
    {
        return null;
    }
}

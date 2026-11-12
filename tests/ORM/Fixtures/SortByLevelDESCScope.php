<?php

// phpcs:ignoreFile
declare(strict_types=1);

namespace Arcano\ORM\Tests\Fixtures;

use Arcano\ORM\Select\ScopeInterface;
use Arcano\ORM\Select\QueryBuilder;

class SortByLevelDESCScope implements ScopeInterface
{
    public function apply(QueryBuilder $query): void
    {
        $query->orderBy('level', 'DESC');
    }
}

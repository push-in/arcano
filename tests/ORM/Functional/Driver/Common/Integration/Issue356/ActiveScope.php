<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Integration\Issue356;

use Arcano\ORM\Select\QueryBuilder;
use Arcano\ORM\Select\ScopeInterface;

class ActiveScope implements ScopeInterface
{
    public function apply(QueryBuilder $query): void
    {
        $query->where('active', true);
    }
}

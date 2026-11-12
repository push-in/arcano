<?php

declare(strict_types=1);

namespace Arcano\ORM\Select\Traits;

use Arcano\ORM\Select\ScopeInterface;
use Arcano\ORM\Select\QueryBuilder;
use Arcano\Database\Query\SelectQuery;

/**
 * Provides the ability to assign the scope to the AbstractLoader.
 *
 * @internal
 */
trait ScopeTrait
{
    protected ?ScopeInterface $scope = null;

    /**
     * Associate scope with the selector.
     */
    public function setScope(?ScopeInterface $scope = null): self
    {
        $this->scope = $scope;

        return $this;
    }

    abstract public function getAlias(): string;

    protected function applyScope(SelectQuery $query): SelectQuery
    {
        $this->scope?->apply(new QueryBuilder($query, $this));

        return $query;
    }
}

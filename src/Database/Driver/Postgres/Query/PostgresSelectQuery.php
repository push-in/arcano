<?php

/**
 * This file is part of Cycle ORM package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Arcano\Database\Driver\Postgres\Query;

use Arcano\Database\Driver\Postgres\Query\Traits\WhereJsonTrait;
use Arcano\Database\Injection\FragmentInterface;
use Arcano\Database\Query\SelectQuery;

class PostgresSelectQuery extends SelectQuery
{
    use WhereJsonTrait;

    /**
     * Apply distinct ON to the query.
     */
    public function distinctOn(FragmentInterface|string $distinctOn): SelectQuery
    {
        $this->distinct = ['on' => $distinctOn];

        return $this;
    }
}

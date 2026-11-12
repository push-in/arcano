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
use Arcano\Database\Query\UpdateQuery;

class PostgresUpdateQuery extends UpdateQuery
{
    use WhereJsonTrait;
}

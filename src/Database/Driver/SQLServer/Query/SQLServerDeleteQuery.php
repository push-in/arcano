<?php

/**
 * This file is part of Cycle ORM package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Arcano\Database\Driver\SQLServer\Query;

use Arcano\Database\Driver\SQLServer\Query\Traits\WhereJsonTrait;
use Arcano\Database\Query\DeleteQuery;

class SQLServerDeleteQuery extends DeleteQuery
{
    use WhereJsonTrait;
}

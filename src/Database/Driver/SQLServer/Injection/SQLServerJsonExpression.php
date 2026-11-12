<?php

/**
 * This file is part of Cycle ORM package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Arcano\Database\Driver\SQLServer\Injection;

use Arcano\Database\Injection\JsonExpression;

abstract class SQLServerJsonExpression extends JsonExpression
{
    protected function getQuotes(): string
    {
        return '[]';
    }
}

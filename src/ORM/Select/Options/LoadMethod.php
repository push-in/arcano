<?php

declare(strict_types=1);

namespace Arcano\ORM\Select\Options;

use Arcano\ORM\Select;

enum LoadMethod: int
{
    /**
     * Load relation data within the same query using JOIN
     * @see Select::SINGLE_QUERY
     */
    case SingleQuery = 1;

    /**
     * Load related data using a separate query
     * @see Select::OUTER_QUERY
     */
    case OuterQuery = 2;
}

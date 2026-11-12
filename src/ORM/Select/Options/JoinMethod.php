<?php

declare(strict_types=1);

namespace Arcano\ORM\Select\Options;

use Arcano\ORM\Select\JoinableLoader;

enum JoinMethod: int
{
    /**
     * INNER JOIN
     * @see JoinableLoader::JOIN
     */
    case InnerJoin = 3;

    /**
     * LEFT JOIN
     * @see JoinableLoader::LEFT_JOIN
     */
    case LeftJoin = 4;
}

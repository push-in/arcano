<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\MySQL\Query;

// phpcs:ignore
use Arcano\Database\Tests\Functional\Driver\Common\Query\NestedQueriesTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class NestedQueriesTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Select;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Select\QueryBuilderTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class QueryBuilderTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

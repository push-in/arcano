<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\MySQL\Schema;

// phpcs:ignore
use Arcano\Database\Tests\Functional\Driver\Common\Schema\JsonColumnTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class JsonColumnTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

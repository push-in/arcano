<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Schema;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Schema\CompositePKTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class CompositePKTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

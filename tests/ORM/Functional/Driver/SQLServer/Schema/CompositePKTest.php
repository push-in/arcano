<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\Schema;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Schema\CompositePKTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class CompositePKTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

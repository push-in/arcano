<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\Schema;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Schema\CommonTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class CommonTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

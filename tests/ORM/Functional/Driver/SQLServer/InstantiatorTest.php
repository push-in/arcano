<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\InstantiatorTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class InstantiatorTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

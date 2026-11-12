<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\SelectorTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class SelectorTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

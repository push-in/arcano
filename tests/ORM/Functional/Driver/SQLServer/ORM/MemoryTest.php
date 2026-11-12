<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\ORM;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\ORM\MemoryTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class MemoryTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

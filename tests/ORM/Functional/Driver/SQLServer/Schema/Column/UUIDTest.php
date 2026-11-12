<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\Schema\Column;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Schema\Column\UUIDTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class UUIDTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

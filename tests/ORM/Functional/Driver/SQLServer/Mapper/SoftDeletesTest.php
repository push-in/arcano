<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\Mapper;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Mapper\SoftDeletesTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class SoftDeletesTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

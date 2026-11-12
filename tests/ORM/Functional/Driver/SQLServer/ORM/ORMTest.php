<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\ORM;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\ORM\ORMTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class ORMTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

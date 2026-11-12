<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\Relation;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\NestedEagerTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class NestedEagerTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\Relation\ManyToMany\Cyclic;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\ManyToMany\Cyclic\CyclicManyToManyTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class CyclicManyToManyTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

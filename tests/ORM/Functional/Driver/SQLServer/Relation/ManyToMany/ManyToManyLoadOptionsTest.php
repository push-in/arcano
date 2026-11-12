<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\Relation\ManyToMany;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\ManyToMany\ManyToManyLoadOptionsTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class ManyToManyLoadOptionsTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

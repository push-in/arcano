<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\Relation;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\LinkedTreeTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class LinkedTreeTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

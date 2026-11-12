<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\Relation;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\ExistingNestedRelationTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class ExistingNestedRelationTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

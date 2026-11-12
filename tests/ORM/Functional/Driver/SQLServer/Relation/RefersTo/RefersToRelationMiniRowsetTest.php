<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\Relation\RefersTo;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\RefersTo\RefersToRelationMiniRowsetTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class RefersToRelationMiniRowsetTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

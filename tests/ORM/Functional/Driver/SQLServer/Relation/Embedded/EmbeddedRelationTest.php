<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\Relation\Embedded;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\Embedded\EmbeddedRelationTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class EmbeddedRelationTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

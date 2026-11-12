<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\Relation\Embedded;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\Embedded\DeepEmbeddedTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class DeepEmbeddedTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

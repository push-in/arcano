<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\Relation;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\BidirectionTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class BidirectionTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

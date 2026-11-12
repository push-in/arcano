<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\Relation\HasOne;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\HasOne\HasOneCompositeKeyTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class HasOneCompositeKeyTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

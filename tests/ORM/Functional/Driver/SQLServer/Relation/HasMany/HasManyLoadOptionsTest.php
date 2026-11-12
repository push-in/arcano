<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\Relation\HasMany;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\HasMany\HasManyLoadOptionsTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class HasManyLoadOptionsTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

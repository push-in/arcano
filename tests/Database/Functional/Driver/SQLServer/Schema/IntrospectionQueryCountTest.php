<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\SQLServer\Schema;

// phpcs:ignore
use Arcano\Database\Tests\Functional\Driver\Common\Schema\IntrospectionQueryCountTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class IntrospectionQueryCountTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

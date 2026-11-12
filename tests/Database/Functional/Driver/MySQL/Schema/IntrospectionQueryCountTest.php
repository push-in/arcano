<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\MySQL\Schema;

// phpcs:ignore
use Arcano\Database\Tests\Functional\Driver\Common\Schema\IntrospectionQueryCountTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class IntrospectionQueryCountTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

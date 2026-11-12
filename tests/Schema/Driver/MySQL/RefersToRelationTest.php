<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\MySQL;

use Arcano\Schema\Tests\Relation\RefersToRelationTest as BaseTest;

/**
 * @group driver
 * @group driver-mysql
 */
class RefersToRelationTest extends BaseTest
{
    public const DRIVER = 'mysql';
}

<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\MySQL;

use Arcano\Schema\Tests\Relation\HasManyRelationTest as BaseTest;

/**
 * @group driver
 * @group driver-mysql
 */
class HasManyRelationTest extends BaseTest
{
    public const DRIVER = 'mysql';
}

<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\MySQL;

use Arcano\Schema\Tests\Relation\ManyToManyRelationTest as BaseTest;

/**
 * @group driver
 * @group driver-mysql
 */
class ManyToManyRelationTest extends BaseTest
{
    public const DRIVER = 'mysql';
}

<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\MySQL;

use Arcano\Schema\Tests\Relation\BelongsToRelationTest as BaseTest;

/**
 * @group driver
 * @group driver-mysql
 */
class BelongsToRelationTest extends BaseTest
{
    public const DRIVER = 'mysql';
}

<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\MySQL;

use Arcano\Schema\Tests\Relation\RefersToRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-mysql
 */
class RefersToRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'mysql';
}

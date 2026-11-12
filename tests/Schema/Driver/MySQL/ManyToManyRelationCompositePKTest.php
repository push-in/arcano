<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\MySQL;

use Arcano\Schema\Tests\Relation\ManyToManyRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-mysql
 */
class ManyToManyRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'mysql';
}

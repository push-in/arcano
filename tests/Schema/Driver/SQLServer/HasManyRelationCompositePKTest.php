<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\SQLServer;

use Arcano\Schema\Tests\Relation\HasManyRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-sqlserver
 */
class HasManyRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'sqlserver';
}

<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\SQLServer;

use Arcano\Schema\Tests\Relation\RefersToRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-sqlserver
 */
class RefersToRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'sqlserver';
}

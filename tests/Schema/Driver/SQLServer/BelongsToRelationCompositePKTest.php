<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\SQLServer;

use Arcano\Schema\Tests\Relation\BelongsToRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-sqlserver
 */
class BelongsToRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'sqlserver';
}

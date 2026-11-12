<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\SQLServer;

use Arcano\Schema\Tests\Relation\Morphed\RefersToMorphedRelationTest as BaseTest;

/**
 * @group driver
 * @group driver-sqlserver
 */
class RefersToMorphedRelationTest extends BaseTest
{
    public const DRIVER = 'sqlserver';
}

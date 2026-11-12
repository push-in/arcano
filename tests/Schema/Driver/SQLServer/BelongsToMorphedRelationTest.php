<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\SQLServer;

use Arcano\Schema\Tests\Relation\Morphed\BelongsToMorphedRelationTest as BaseTest;

/**
 * @group driver
 * @group driver-sqlserver
 */
class BelongsToMorphedRelationTest extends BaseTest
{
    public const DRIVER = 'sqlserver';
}

<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\SQLite;

use Arcano\Schema\Tests\Relation\Morphed\MorphedHasOneRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-sqlite
 */
class MorphedHasOneRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'sqlite';
}

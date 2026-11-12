<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\SQLite;

use Arcano\Schema\Tests\Relation\Morphed\BelongsToMorphedRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-sqlite
 */
class BelongsToMorphedRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'sqlite';
}

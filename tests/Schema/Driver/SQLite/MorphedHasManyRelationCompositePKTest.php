<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\SQLite;

use Arcano\Schema\Tests\Relation\Morphed\MorphedHasManyRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-sqlite
 */
class MorphedHasManyRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'sqlite';
}

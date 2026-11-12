<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\MySQL;

use Arcano\Schema\Tests\Relation\Morphed\BelongsToMorphedRelationTest as BaseTest;

/**
 * @group driver
 * @group driver-mysql
 */
class BelongsToMorphedRelationTest extends BaseTest
{
    public const DRIVER = 'mysql';
}

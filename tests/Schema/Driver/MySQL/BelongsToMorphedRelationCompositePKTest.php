<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\MySQL;

use Arcano\Schema\Tests\Relation\Morphed\BelongsToMorphedRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-mysql
 */
class BelongsToMorphedRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'mysql';
}

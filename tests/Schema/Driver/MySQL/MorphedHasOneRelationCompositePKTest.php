<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\MySQL;

use Arcano\Schema\Tests\Relation\Morphed\MorphedHasOneRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-mysql
 */
class MorphedHasOneRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'mysql';
}

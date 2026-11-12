<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\MySQL;

use Arcano\Schema\Tests\Relation\Morphed\RefersToMorphedRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-mysql
 */
class RefersToMorphedRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'mysql';
}

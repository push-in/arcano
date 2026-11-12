<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\MySQL;

use Arcano\Schema\Tests\Relation\Morphed\MorphedHasManyRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-mysql
 */
class MorphedHasManyRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'mysql';
}

<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\MySQL;

use Arcano\Schema\Tests\Relation\Morphed\MorphedHasManyRelationTest as BaseTest;

/**
 * @group driver
 * @group driver-mysql
 */
class MorphedHasManyRelationTest extends BaseTest
{
    public const DRIVER = 'mysql';
}

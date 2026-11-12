<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\Postgres;

use Arcano\Schema\Tests\Relation\Morphed\MorphedHasManyRelationTest as BaseTest;

/**
 * @group driver
 * @group driver-postgres
 */
class MorphedHasManyRelationTest extends BaseTest
{
    public const DRIVER = 'postgres';
}

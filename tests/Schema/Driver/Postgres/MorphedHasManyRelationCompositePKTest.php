<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\Postgres;

use Arcano\Schema\Tests\Relation\Morphed\MorphedHasManyRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-postgres
 */
class MorphedHasManyRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'postgres';
}

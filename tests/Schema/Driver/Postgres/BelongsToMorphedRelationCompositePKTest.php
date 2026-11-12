<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\Postgres;

use Arcano\Schema\Tests\Relation\Morphed\BelongsToMorphedRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-postgres
 */
class BelongsToMorphedRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'postgres';
}

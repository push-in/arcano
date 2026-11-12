<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\Postgres;

use Arcano\Schema\Tests\Relation\Morphed\RefersToMorphedRelationTest as BaseTest;

/**
 * @group driver
 * @group driver-postgres
 */
class RefersToMorphedRelationTest extends BaseTest
{
    public const DRIVER = 'postgres';
}

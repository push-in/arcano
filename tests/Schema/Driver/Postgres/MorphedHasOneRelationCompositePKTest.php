<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\Postgres;

use Arcano\Schema\Tests\Relation\Morphed\MorphedHasOneRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-postgres
 */
class MorphedHasOneRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'postgres';
}

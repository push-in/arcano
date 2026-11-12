<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\Postgres;

use Arcano\Schema\Tests\Relation\BelongsToRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-postgres
 */
class BelongsToRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'postgres';
}

<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\Postgres;

use Arcano\Schema\Tests\Relation\RefersToRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-postgres
 */
class RefersToRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'postgres';
}

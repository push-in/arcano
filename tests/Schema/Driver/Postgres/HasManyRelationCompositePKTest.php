<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\Postgres;

use Arcano\Schema\Tests\Relation\HasManyRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-postgres
 */
class HasManyRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'postgres';
}

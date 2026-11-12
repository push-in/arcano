<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\Postgres;

use Arcano\Schema\Tests\Relation\ManyToManyRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-postgres
 */
class ManyToManyRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'postgres';
}

<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\SQLite;

use Arcano\Schema\Tests\Relation\HasManyRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-sqlite
 */
class HasManyRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'sqlite';
}

<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\SQLite;

use Arcano\Schema\Tests\Relation\HasOneRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-sqlite
 */
class HasOneRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'sqlite';
}

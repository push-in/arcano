<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\SQLite;

use Arcano\Schema\Tests\Relation\ManyToManyRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-sqlite
 */
class ManyToManyRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'sqlite';
}

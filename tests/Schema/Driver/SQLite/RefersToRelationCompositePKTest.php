<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\SQLite;

use Arcano\Schema\Tests\Relation\RefersToRelationCompositePKTest as BaseTest;

/**
 * @group driver
 * @group driver-sqlite
 */
class RefersToRelationCompositePKTest extends BaseTest
{
    public const DRIVER = 'sqlite';
}

<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\SQLite;

use Arcano\Schema\Tests\Relation\EmbeddedTest as BaseTest;

/**
 * @group driver
 * @group driver-sqlite
 */
class EmbeddedTest extends BaseTest
{
    public const DRIVER = 'sqlite';
}

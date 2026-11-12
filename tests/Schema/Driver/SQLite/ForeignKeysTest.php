<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\SQLite;

use Arcano\Schema\Tests\Generator\ForeignKeysTest as BaseTest;

/**
 * @group driver
 * @group driver-sqlite
 */
class ForeignKeysTest extends BaseTest
{
    public const DRIVER = 'sqlite';
}

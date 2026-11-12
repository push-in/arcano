<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\SQLite;

use Arcano\Schema\Tests\Generator\TableGeneratorTest as BaseTest;

/**
 * @group driver
 * @group driver-sqlite
 */
class TableGeneratorTest extends BaseTest
{
    public const DRIVER = 'sqlite';
}

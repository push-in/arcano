<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\SQLite;

use Arcano\Schema\Tests\Generator\GenerateRelationsTest as BaseTest;

/**
 * @group driver
 * @group driver-sqlite
 */
class GenerateRelationsTest extends BaseTest
{
    public const DRIVER = 'sqlite';
}

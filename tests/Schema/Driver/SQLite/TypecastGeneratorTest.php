<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\SQLite;

use Arcano\Schema\Tests\Generator\TypecastGeneratorTest as BaseTest;

/**
 * @group driver
 * @group driver-sqlite
 */
class TypecastGeneratorTest extends BaseTest
{
    public const DRIVER = 'sqlite';
}

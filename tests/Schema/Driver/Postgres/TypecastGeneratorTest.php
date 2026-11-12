<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\Postgres;

use Arcano\Schema\Tests\Generator\TypecastGeneratorTest as BaseTest;

/**
 * @group driver
 * @group driver-postgres
 */
class TypecastGeneratorTest extends BaseTest
{
    public const DRIVER = 'postgres';
}

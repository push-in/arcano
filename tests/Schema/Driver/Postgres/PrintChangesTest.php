<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\Postgres;

use Arcano\Schema\Tests\Generator\PrintChangesTest as BaseTest;

/**
 * @group driver
 * @group driver-postgres
 */
class PrintChangesTest extends BaseTest
{
    public const DRIVER = 'postgres';
}

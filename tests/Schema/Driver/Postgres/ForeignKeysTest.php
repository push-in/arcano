<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\Postgres;

use Arcano\Schema\Tests\Generator\ForeignKeysTest as BaseTest;

/**
 * @group driver
 * @group driver-postgres
 */
class ForeignKeysTest extends BaseTest
{
    public const DRIVER = 'postgres';
}

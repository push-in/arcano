<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\Postgres;

use Arcano\Schema\Tests\Generator\RenderRelationsTest as BaseTest;

/**
 * @group driver
 * @group driver-postgres
 */
class RenderRelationsTest extends BaseTest
{
    public const DRIVER = 'postgres';
}

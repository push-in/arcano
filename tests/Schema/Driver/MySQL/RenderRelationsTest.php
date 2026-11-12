<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\MySQL;

use Arcano\Schema\Tests\Generator\RenderRelationsTest as BaseTest;

/**
 * @group driver
 * @group driver-mysql
 */
class RenderRelationsTest extends BaseTest
{
    public const DRIVER = 'mysql';
}

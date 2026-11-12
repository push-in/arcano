<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\MySQL;

use Arcano\Schema\Tests\Generator\TableGeneratorTest as BaseTest;

/**
 * @group driver
 * @group driver-mysql
 */
class TableGeneratorTest extends BaseTest
{
    public const DRIVER = 'mysql';
}

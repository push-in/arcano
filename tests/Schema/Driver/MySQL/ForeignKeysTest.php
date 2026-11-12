<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\MySQL;

use Arcano\Schema\Tests\Generator\ForeignKeysTest as BaseTest;

/**
 * @group driver
 * @group driver-mysql
 */
class ForeignKeysTest extends BaseTest
{
    public const DRIVER = 'mysql';
}

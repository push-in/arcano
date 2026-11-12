<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\SQLServer;

use Arcano\Schema\Tests\Generator\ForeignKeysTest as BaseTest;

/**
 * @group driver
 * @group driver-sqlserver
 */
class ForeignKeysTest extends BaseTest
{
    public const DRIVER = 'sqlserver';
}

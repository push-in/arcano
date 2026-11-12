<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Arcano\Schema\Generator\Migrations\Tests\Functional\Driver\SQLServer;

use Arcano\Schema\Generator\Migrations\Tests\Functional\MultipleFileStrategyTest as CommonTestCase;

/**
 * @group driver
 * @group driver-sqlserver
 */
final class MultipleFileStrategyTest extends CommonTestCase
{
    public const DRIVER = "sqlserver";
}

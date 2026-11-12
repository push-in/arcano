<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Arcano\Schema\Generator\Migrations\Tests\Functional\Driver\MySQL;

use Arcano\Schema\Generator\Migrations\Tests\Functional\SingleFileStrategyTest as CommonTestCase;

/**
 * @group driver
 * @group driver-mysql
 */
final class SingleFileStrategyTest extends CommonTestCase
{
    public const DRIVER = "mysql";
}

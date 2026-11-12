<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Arcano\Schema\Generator\Migrations\Tests\Functional\Driver\SQLite;

use Arcano\Schema\Generator\Migrations\Tests\Functional\MultipleFileStrategyTest as CommonTestCase;

/**
 * @group driver
 * @group driver-sqlite
 */
final class MultipleFileStrategyTest extends CommonTestCase
{
    public const DRIVER = "sqlite";
}

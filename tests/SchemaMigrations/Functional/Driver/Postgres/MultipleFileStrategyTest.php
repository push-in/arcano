<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Arcano\Schema\Generator\Migrations\Tests\Functional\Driver\Postgres;

use Arcano\Schema\Generator\Migrations\Tests\Functional\MultipleFileStrategyTest as CommonTestCase;

/**
 * @group driver
 * @group driver-postgres
 */
final class MultipleFileStrategyTest extends CommonTestCase
{
    public const DRIVER = "postgres";
}

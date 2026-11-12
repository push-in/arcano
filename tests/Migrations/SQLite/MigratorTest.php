<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Arcano\Migrations\Tests\SQLite;

/**
 * @group driver
 * @group driver-sqlite
 */
class MigratorTest extends \Arcano\Migrations\Tests\MigratorTest
{
    public const DRIVER = 'sqlite';
}

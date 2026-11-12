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
class AtomizerTest extends \Arcano\Migrations\Tests\AtomizerTest
{
    public const DRIVER = 'sqlite';
}

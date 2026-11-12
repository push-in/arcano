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
class ComplexAtomizerTest extends \Arcano\Migrations\Tests\ComplexAtomizerTest
{
    public const DRIVER = 'sqlite';
}

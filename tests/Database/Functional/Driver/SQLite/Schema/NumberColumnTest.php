<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\SQLite\Schema;

// phpcs:ignore
use Arcano\Database\Tests\Functional\Driver\Common\Schema\NumberColumnTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
final class NumberColumnTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

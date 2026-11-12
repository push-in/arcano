<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\SQLite\Schema;

// phpcs:ignore
use Arcano\Database\Tests\Functional\Driver\Common\Schema\DefaultValueTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class DefaultValueTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

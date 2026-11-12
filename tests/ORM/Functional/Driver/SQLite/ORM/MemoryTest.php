<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLite\ORM;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\ORM\MemoryTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class MemoryTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

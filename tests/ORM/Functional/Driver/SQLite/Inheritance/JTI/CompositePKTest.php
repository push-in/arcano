<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLite\Inheritance\JTI;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Inheritance\JTI\CompositePKTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class CompositePKTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

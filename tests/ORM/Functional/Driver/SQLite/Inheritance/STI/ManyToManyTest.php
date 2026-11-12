<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLite\Inheritance\STI;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Inheritance\STI\ManyToManyTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class ManyToManyTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

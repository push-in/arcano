<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLite\Relation\HasOne;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\HasOne\HasOneCyclicTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class HasOneCyclicTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

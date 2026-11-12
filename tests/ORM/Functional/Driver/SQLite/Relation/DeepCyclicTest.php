<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLite\Relation;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\DeepCyclicTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class DeepCyclicTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

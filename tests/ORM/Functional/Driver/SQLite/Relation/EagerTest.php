<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLite\Relation;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\EagerTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class EagerTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

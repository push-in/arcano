<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLite\Relation\Morphed;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\Morphed\MorphedHasOneRelationTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class MorphedHasOneRelationTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLite\Relation\Morphed;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\Morphed\MorphedHasManyRelationTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class MorphedHasManyRelationTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

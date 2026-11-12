<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\Relation\Morphed;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\Morphed\BelongsToMorphedRelationTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class BelongsToMorphedRelationTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

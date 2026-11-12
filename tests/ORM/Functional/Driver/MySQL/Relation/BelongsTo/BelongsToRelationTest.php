<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Relation\BelongsTo;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\BelongsTo\BelongsToRelationTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class BelongsToRelationTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

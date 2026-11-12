<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Relation\BelongsTo;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\BelongsTo\BelongsToNullableRelationTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class BelongsToNullableRelationTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

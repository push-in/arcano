<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Relation\BelongsTo;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\BelongsTo\BelongsToRelationWithNestedUuidTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class BelongsToRelationWithNestedUuidTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

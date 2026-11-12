<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLite\Relation\BelongsTo;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\BelongsTo\BelongsToWithHasOneTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class BelongsToWithHasOneTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

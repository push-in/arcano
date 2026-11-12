<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Relation\BelongsTo;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\BelongsTo\BelongsToLoadOptionsTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class BelongsToLoadOptionsTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

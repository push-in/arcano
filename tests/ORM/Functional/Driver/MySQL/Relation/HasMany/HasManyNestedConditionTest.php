<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Relation\HasMany;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\HasMany\HasManyNestedConditionTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class HasManyNestedConditionTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

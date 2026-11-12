<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Relation\HasOne;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\HasOne\HasOneRelationTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class HasOneRelationTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

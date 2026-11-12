<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Relation;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\InverseRelationTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class InverseRelationTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Relation\ManyToMany\Cyclic;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\ManyToMany\Cyclic\CyclicManyToManyTypedTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class CyclicManyToManyTypedTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

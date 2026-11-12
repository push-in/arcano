<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Relation\ManyToMany;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\ManyToMany\ManyToManyScopedTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class ManyToManyScopedTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

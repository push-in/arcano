<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\ORM;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\ORM\ORMTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class ORMTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

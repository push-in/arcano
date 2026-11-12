<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\ORM;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\ORM\MemoryTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class MemoryTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

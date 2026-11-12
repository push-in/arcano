<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Select;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Select\SelectFromTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class SelectFromTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

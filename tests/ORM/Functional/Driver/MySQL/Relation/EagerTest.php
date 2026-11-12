<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Relation;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\EagerTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class EagerTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

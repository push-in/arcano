<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Relation;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\BidirectionTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class BidirectionTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

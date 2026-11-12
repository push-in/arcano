<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Transaction;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Transaction\OptimisticLockTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class OptimisticLockTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLite\Transaction;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Transaction\UnitOfWorkTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class UnitOfWorkTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

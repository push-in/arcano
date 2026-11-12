<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Postgres\Transaction;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Transaction\UnitOfWorkTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class UnitOfWorkTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

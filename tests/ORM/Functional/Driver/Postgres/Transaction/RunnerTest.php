<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Postgres\Transaction;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Transaction\RunnerTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class RunnerTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

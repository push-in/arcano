<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Postgres;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\InstantiatorTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class InstantiatorTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

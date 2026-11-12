<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Postgres\Typecast;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Typecast\UUIDTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class UUIDTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

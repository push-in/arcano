<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Postgres\Mapper;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Mapper\UUIDTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class UUIDTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Postgres\Relation\HasMany;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\HasMany\HasManyLoadingTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class HasManyLoadingTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

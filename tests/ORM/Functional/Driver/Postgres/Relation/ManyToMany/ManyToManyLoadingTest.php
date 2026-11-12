<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Postgres\Relation\ManyToMany;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\ManyToMany\ManyToManyLoadingTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class ManyToManyLoadingTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

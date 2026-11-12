<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Postgres\Relation\ManyToMany;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\ManyToMany\ManyToManyScopedPivotTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class ManyToManyScopedPivotTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

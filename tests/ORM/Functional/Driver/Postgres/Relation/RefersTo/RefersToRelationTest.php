<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Postgres\Relation\RefersTo;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\RefersTo\RefersToRelationTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class RefersToRelationTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Postgres\Relation\RefersTo;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\RefersTo\RefersToRelationCompositeKeyTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class RefersToRelationCompositeKeyTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

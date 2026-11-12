<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Postgres\Relation\Embedded;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\Embedded\EmbeddedRelationTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class EmbeddedRelationTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

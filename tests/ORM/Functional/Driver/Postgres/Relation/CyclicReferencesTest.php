<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Postgres\Relation;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\CyclicReferencesTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class CyclicReferencesTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

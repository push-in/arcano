<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Postgres\Classless;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Classless\ClasslessInverseRelationTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class ClasslessInverseRelationTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

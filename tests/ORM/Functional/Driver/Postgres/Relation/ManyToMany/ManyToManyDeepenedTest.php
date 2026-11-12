<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Postgres\Relation\ManyToMany;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\ManyToMany\ManyToManyDeepenedTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class ManyToManyDeepenedTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

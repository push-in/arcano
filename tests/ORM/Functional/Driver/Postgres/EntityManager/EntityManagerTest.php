<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Postgres\EntityManager;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\EntityManager\EntityManagerTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class EntityManagerTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

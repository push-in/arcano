<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\EntityManager;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\EntityManager\EntityManagerTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class EntityManagerTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\Select;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Select\PersistRepositoryTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class PersistRepositoryTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

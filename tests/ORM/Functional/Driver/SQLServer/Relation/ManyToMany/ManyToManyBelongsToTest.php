<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\Relation\ManyToMany;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\ManyToMany\ManyToManyBelongsToTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class ManyToManyBelongsToTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

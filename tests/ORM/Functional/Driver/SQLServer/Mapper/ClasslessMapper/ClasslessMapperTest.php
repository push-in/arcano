<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\Mapper\ClasslessMapper;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Mapper\ClasslessMapper\ClasslessMapperTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class ClasslessMapperTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

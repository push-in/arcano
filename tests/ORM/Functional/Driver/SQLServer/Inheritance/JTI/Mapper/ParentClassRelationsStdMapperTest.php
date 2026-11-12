<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\Inheritance\JTI\Mapper;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Inheritance\JTI\Mapper\ParentClassRelationsStdMapperTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class ParentClassRelationsStdMapperTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

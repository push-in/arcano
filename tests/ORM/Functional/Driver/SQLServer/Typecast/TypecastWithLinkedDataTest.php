<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\Typecast;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Typecast\TypecastWithLinkedDataTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class TypecastWithLinkedDataTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLServer\Relation\BelongsTo;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\BelongsTo\BelongsToProxyMapperTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlserver
 */
class BelongsToProxyMapperTest extends CommonClass
{
    public const DRIVER = 'sqlserver';
}

<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Postgres\Relation\HasOne;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\HasOne\HasOneLoadOptionsTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class HasOneLoadOptionsTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

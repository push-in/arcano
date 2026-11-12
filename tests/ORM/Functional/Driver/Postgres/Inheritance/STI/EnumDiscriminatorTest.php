<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Postgres\Inheritance\STI;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Inheritance\STI\EnumDiscriminatorTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class EnumDiscriminatorTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

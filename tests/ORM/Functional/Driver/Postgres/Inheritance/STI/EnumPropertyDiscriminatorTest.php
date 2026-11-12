<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Postgres\Inheritance\STI;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Inheritance\STI\EnumPropertyDiscriminatorTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class EnumPropertyDiscriminatorTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

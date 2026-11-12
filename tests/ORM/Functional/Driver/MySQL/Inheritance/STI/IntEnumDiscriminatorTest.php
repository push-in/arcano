<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Inheritance\STI;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Inheritance\STI\IntEnumDiscriminatorTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class IntEnumDiscriminatorTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Classless;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Classless\ClasslessCyclicReferencesTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class ClasslessCyclicReferencesTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

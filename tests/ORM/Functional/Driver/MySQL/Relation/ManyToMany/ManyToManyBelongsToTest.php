<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Relation\ManyToMany;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\ManyToMany\ManyToManyBelongsToTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class ManyToManyBelongsToTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

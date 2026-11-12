<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Relation\Morphed;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\Morphed\MorphedHasManyPromiseTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class MorphedHasManyPromiseTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

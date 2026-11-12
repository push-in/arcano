<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLite\Relation\HasMany\Cyclic;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\HasMany\Cyclic\CyclicHasManyReferencesTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class CyclicHasManyReferencesTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

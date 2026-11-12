<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Postgres\Relation\Morphed;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\Morphed\MorphedHasOneLoadOptionsTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class MorphedHasOneLoadOptionsTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

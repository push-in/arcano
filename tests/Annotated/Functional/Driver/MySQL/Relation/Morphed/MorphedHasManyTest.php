<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\MySQL\Relation\Morphed;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Relation\Morphed\MorphedHasManyTestCase;

/**
 * @group driver
 * @group driver-mysql
 */
final class MorphedHasManyTest extends MorphedHasManyTestCase
{
    public const DRIVER = 'mysql';
}

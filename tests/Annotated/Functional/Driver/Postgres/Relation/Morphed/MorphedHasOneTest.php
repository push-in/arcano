<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Postgres\Relation\Morphed;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Relation\Morphed\MorphedHasOneTestCase;

/**
 * @group driver
 * @group driver-postgres
 */
final class MorphedHasOneTest extends MorphedHasOneTestCase
{
    public const DRIVER = 'postgres';
}

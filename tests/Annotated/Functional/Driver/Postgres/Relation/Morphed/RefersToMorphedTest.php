<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Postgres\Relation\Morphed;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Relation\Morphed\RefersToMorphedTestCase;

/**
 * @group driver
 * @group driver-postgres
 */
final class RefersToMorphedTest extends RefersToMorphedTestCase
{
    public const DRIVER = 'postgres';
}

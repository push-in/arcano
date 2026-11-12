<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\SQLite\Relation\Morphed;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Relation\Morphed\RefersToMorphedTestCase;

/**
 * @group driver
 * @group driver-sqlite
 */
final class RefersToMorphedTest extends RefersToMorphedTestCase
{
    public const DRIVER = 'sqlite';
}

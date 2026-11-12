<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\SQLite\Relation;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Relation\BelongsToTestCase;

/**
 * @group driver
 * @group driver-sqlite
 */
final class BelongsToTest extends BelongsToTestCase
{
    public const DRIVER = 'sqlite';
}

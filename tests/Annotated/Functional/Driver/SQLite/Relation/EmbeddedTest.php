<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\SQLite\Relation;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Relation\EmbeddedTestCase;

/**
 * @group driver
 * @group driver-sqlite
 */
final class EmbeddedTest extends EmbeddedTestCase
{
    public const DRIVER = 'sqlite';
}

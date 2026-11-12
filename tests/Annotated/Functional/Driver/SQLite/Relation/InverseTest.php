<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\SQLite\Relation;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Relation\InverseTestCase;

/**
 * @group driver
 * @group driver-sqlite
 */
final class InverseTest extends InverseTestCase
{
    public const DRIVER = 'sqlite';
}

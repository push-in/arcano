<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\SQLite\Relation;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Relation\CompositeKeysTestCase;

/**
 * @group driver
 * @group driver-sqlite
 */
final class CompositeKeysTest extends CompositeKeysTestCase
{
    public const DRIVER = 'sqlite';
}

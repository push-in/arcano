<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\SQLite\Relation;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Relation\HasOneTestCase;

/**
 * @group driver
 * @group driver-sqlite
 */
final class HasOneTest extends HasOneTestCase
{
    public const DRIVER = 'sqlite';
}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\SQLite;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\ChildTestCase;

/**
 * @group driver
 * @group driver-sqlite
 */
final class ChildTest extends ChildTestCase
{
    public const DRIVER = 'sqlite';
}

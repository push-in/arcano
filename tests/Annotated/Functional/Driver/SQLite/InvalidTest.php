<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\SQLite;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\InvalidTestCase;

/**
 * @group driver
 * @group driver-sqlite
 */
final class InvalidTest extends InvalidTestCase
{
    public const DRIVER = 'sqlite';
}

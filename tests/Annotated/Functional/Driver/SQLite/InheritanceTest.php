<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\SQLite;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\InheritanceTestCase;

/**
 * @group driver
 * @group driver-sqlite
 */
final class InheritanceTest extends InheritanceTestCase
{
    public const DRIVER = 'sqlite';
}

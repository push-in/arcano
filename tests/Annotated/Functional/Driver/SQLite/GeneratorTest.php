<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\SQLite;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\GeneratorTestCase;

/**
 * @group driver
 * @group driver-sqlite
 */
final class GeneratorTest extends GeneratorTestCase
{
    public const DRIVER = 'sqlite';
}

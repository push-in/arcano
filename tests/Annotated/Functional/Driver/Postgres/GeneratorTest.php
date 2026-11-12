<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Postgres;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\GeneratorTestCase;

/**
 * @group driver
 * @group driver-postgres
 */
final class GeneratorTest extends GeneratorTestCase
{
    public const DRIVER = 'postgres';
}

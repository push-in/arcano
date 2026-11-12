<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Postgres;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\InvalidTestCase;

/**
 * @group driver
 * @group driver-postgres
 */
final class InvalidTest extends InvalidTestCase
{
    public const DRIVER = 'postgres';
}

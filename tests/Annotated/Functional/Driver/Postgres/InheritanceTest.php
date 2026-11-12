<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Postgres;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\InheritanceTestCase;

/**
 * @group driver
 * @group driver-postgres
 */
final class InheritanceTest extends InheritanceTestCase
{
    public const DRIVER = 'postgres';
}

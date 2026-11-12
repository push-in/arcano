<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Postgres;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\TypecastTestCase;

/**
 * @group driver
 * @group driver-postgres
 */
final class TypecastTest extends TypecastTestCase
{
    public const DRIVER = 'postgres';
}

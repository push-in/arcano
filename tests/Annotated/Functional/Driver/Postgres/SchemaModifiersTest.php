<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Postgres;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\SchemaModifiersTestCase;

/**
 * @group driver
 * @group driver-postgres
 */
final class SchemaModifiersTest extends SchemaModifiersTestCase
{
    public const DRIVER = 'postgres';
}

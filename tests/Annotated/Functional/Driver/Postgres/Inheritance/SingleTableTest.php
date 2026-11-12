<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Postgres\Inheritance;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Inheritance\SingleTableTestCase;

/**
 * @group driver
 * @group driver-postgres
 */
final class SingleTableTest extends SingleTableTestCase
{
    public const DRIVER = 'postgres';
}

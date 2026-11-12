<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Postgres\Inheritance;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Inheritance\JoinedTableTestCase;

/**
 * @group driver
 * @group driver-postgres
 */
final class JoinedTableTest extends JoinedTableTestCase
{
    public const DRIVER = 'postgres';
}

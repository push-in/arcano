<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Postgres;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\TableTestCase;

/**
 * @group driver
 * @group driver-postgres
 */
final class TableTest extends TableTestCase
{
    public const DRIVER = 'postgres';
}

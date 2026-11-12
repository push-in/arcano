<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\SQLite;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\TableTestCase;

/**
 * @group driver
 * @group driver-sqlite
 */
final class TableTest extends TableTestCase
{
    public const DRIVER = 'sqlite';
}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\SQLServer;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\TableTestCase;

/**
 * @group driver
 * @group driver-sqlserver
 */
final class TableTest extends TableTestCase
{
    public const DRIVER = 'sqlserver';
}

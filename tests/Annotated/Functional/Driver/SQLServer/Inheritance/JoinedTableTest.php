<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\SQLServer\Inheritance;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Inheritance\JoinedTableTestCase;

/**
 * @group driver
 * @group driver-sqlserver
 */
final class JoinedTableTest extends JoinedTableTestCase
{
    public const DRIVER = 'sqlserver';
}

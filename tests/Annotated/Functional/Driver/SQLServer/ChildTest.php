<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\SQLServer;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\ChildTestCase;

/**
 * @group driver
 * @group driver-sqlserver
 */
final class ChildTest extends ChildTestCase
{
    public const DRIVER = 'sqlserver';
}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\SQLServer;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\SchemaModifiersTestCase;

/**
 * @group driver
 * @group driver-sqlserver
 */
final class SchemaModifiersTest extends SchemaModifiersTestCase
{
    public const DRIVER = 'sqlserver';
}

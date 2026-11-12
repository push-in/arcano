<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\MySQL;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\SchemaModifiersTestCase;

/**
 * @group driver
 * @group driver-mysql
 */
final class SchemaModifiersTest extends SchemaModifiersTestCase
{
    public const DRIVER = 'mysql';
}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\SQLServer\Relation;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Relation\EmbeddedTestCase;

/**
 * @group driver
 * @group driver-sqlserver
 */
final class EmbeddedTest extends EmbeddedTestCase
{
    public const DRIVER = 'sqlserver';
}

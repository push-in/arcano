<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\SQLServer\Relation;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Relation\HasOneTestCase;

/**
 * @group driver
 * @group driver-sqlserver
 */
final class HasOneTest extends HasOneTestCase
{
    public const DRIVER = 'sqlserver';
}

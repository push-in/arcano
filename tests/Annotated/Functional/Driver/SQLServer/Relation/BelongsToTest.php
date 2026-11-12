<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\SQLServer\Relation;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Relation\BelongsToTestCase;

/**
 * @group driver
 * @group driver-sqlserver
 */
final class BelongsToTest extends BelongsToTestCase
{
    public const DRIVER = 'sqlserver';
}

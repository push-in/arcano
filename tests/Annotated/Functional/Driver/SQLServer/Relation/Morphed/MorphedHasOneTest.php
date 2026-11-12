<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\SQLServer\Relation\Morphed;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Relation\Morphed\MorphedHasOneTestCase;

/**
 * @group driver
 * @group driver-sqlserver
 */
final class MorphedHasOneTest extends MorphedHasOneTestCase
{
    public const DRIVER = 'sqlserver';
}

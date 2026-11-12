<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\SQLServer\Relation\Morphed;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Relation\Morphed\RefersToMorphedTestCase;

/**
 * @group driver
 * @group driver-sqlserver
 */
final class RefersToMorphedTest extends RefersToMorphedTestCase
{
    public const DRIVER = 'sqlserver';
}

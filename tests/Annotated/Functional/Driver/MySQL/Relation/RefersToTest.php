<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\MySQL\Relation;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Relation\RefersToTestCase;

/**
 * @group driver
 * @group driver-mysql
 */
final class RefersToTest extends RefersToTestCase
{
    public const DRIVER = 'mysql';
}

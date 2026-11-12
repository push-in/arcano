<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\MySQL\Relation;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Relation\BelongsToTestCase;

/**
 * @group driver
 * @group driver-mysql
 */
final class BelongsToTest extends BelongsToTestCase
{
    public const DRIVER = 'mysql';
}

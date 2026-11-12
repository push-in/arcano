<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\MySQL\Relation;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Relation\ManyToManyTestCase;

/**
 * @group driver
 * @group driver-mysql
 */
final class ManyToManyTest extends ManyToManyTestCase
{
    public const DRIVER = 'mysql';
}

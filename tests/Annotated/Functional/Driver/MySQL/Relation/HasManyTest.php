<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\MySQL\Relation;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Relation\HasManyTestCase;

/**
 * @group driver
 * @group driver-mysql
 */
final class HasManyTest extends HasManyTestCase
{
    public const DRIVER = 'mysql';
}

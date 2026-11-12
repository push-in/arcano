<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Postgres\Relation;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Relation\HasManyTestCase;

/**
 * @group driver
 * @group driver-postgres
 */
final class HasManyTest extends HasManyTestCase
{
    public const DRIVER = 'postgres';
}

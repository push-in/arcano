<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Postgres\Relation;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Relation\HasOneTestCase;

/**
 * @group driver
 * @group driver-postgres
 */
final class HasOneTest extends HasOneTestCase
{
    public const DRIVER = 'postgres';
}

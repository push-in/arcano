<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Postgres\Relation;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Relation\InverseTestCase;

/**
 * @group driver
 * @group driver-postgres
 */
final class InverseTest extends InverseTestCase
{
    public const DRIVER = 'postgres';
}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Postgres;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\ChildTestCase;

/**
 * @group driver
 * @group driver-postgres
 */
final class ChildTest extends ChildTestCase
{
    public const DRIVER = 'postgres';
}

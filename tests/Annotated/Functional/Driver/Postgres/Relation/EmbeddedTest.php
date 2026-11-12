<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Postgres\Relation;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Relation\EmbeddedTestCase;

/**
 * @group driver
 * @group driver-postgres
 */
final class EmbeddedTest extends EmbeddedTestCase
{
    public const DRIVER = 'postgres';
}

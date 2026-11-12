<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Postgres;

use Arcano\Annotated\Tests\Functional\Driver\Common\CompositePkGeneratedTestCase;

/**
 * @group driver
 * @group driver-postgres
 */
final class CompositePkGeneratedTest extends CompositePkGeneratedTestCase
{
    public const DRIVER = 'postgres';
}

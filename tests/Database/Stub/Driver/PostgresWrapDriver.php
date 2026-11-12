<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Stub\Driver;

use Arcano\Database\Driver\Postgres\PostgresDriver;

class PostgresWrapDriver extends PostgresDriver
{
    use TestDriverTrait;
}

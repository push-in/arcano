<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Stub\Driver;

use Arcano\Database\Driver\MySQL\MySQLDriver;

class MysqlWrapDriver extends MySQLDriver
{
    use TestDriverTrait;
}

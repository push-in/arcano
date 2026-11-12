<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Unit\Driver\SQLServer\Query;

use Arcano\Database\Driver\DriverInterface;
use Arcano\Database\Driver\SQLServer\Query\SQLServerInsertQuery;
use Arcano\Database\Exception\BuilderException;
use PHPUnit\Framework\TestCase;

final class SQLServerInsertQueryTest extends TestCase
{
    public function testWithDriverException(): void
    {
        $insert = new SQLServerInsertQuery();

        $this->expectException(BuilderException::class);
        $insert->withDriver($this->createMock(DriverInterface::class));
    }
}

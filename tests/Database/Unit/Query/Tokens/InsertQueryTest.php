<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Unit\Query\Tokens;

use PHPUnit\Framework\TestCase;
use Arcano\Database\Driver\CompilerInterface;
use Arcano\Database\Injection\Parameter;
use Arcano\Database\Query\InsertQuery;

class InsertQueryTest extends TestCase
{
    public function testBuildQuery(): void
    {
        $insert = new InsertQuery();
        $insert
            ->into('table')
            ->columns('name', 'value')
            ->values(['Antony', 1]);

        $this->assertSame(
            CompilerInterface::INSERT_QUERY,
            $insert->getType(),
        );

        $this->assertEquals(
            [
                'table' => 'table',
                'columns' => ['name', 'value'],
                'values' => [new Parameter(['Antony', 1])],
                'onConflict' => null,
            ],
            $insert->getTokens(),
        );
    }
}

<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Unit\Query\Tokens;

use PHPUnit\Framework\TestCase;
use Arcano\Database\Driver\CompilerInterface;
use Arcano\Database\Injection\Parameter;
use Arcano\Database\Query\DeleteQuery;

class DeleteQueryTest extends TestCase
{
    public function testBuildQuery(): void
    {
        $delete = new DeleteQuery();
        $delete
            ->from('table')
            ->where(['name' => 'Antony'])
            ->orWhere('id', '>', 1);

        $this->assertSame(
            CompilerInterface::DELETE_QUERY,
            $delete->getType(),
        );

        $this->assertEquals(
            [
                'table' => 'table',
                'where' => [
                    [
                        'AND',
                        ['name', '=', new Parameter('Antony')],
                    ],
                    [
                        'OR',
                        ['id', '>', new Parameter(1)],
                    ],
                ],
            ],
            $delete->getTokens(),
        );
    }
}

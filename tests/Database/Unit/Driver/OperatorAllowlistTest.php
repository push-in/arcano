<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Unit\Driver;

use Arcano\Database\Driver\MySQL\MySQLCompiler;
use Arcano\Database\Exception\CompilerException;
use Arcano\Database\Injection\Fragment;
use Arcano\Database\Query\QueryParameters;
use Arcano\Database\Query\SelectQuery;
use PHPUnit\Framework\TestCase;

/** A where operator is SQL text: only known operators may reach the statement. */
final class OperatorAllowlistTest extends TestCase
{
    public static function injectedOperators(): \Traversable
    {
        yield ['= 1 OR 1=1 OR id ='];
        yield ['=1;DROP TABLE users;--'];
        yield ['LIKE/**/'];
        yield [''];
    }

    public static function knownOperators(): \Traversable
    {
        foreach (['=', '!=', '<>', '>', '<', '>=', '<=', '<=>', 'like', 'NOT LIKE', 'ilike', 'regexp', 'is', 'IS NOT', '@>', '<@', '~*'] as $operator) {
            yield [$operator];
        }
    }

    /** @dataProvider injectedOperators */
    public function testRejectsOperatorsThatAreNotOperators(string $operator): void
    {
        $this->expectException(CompilerException::class);
        $this->compile((new SelectQuery())->from('users')->where('id', $operator, 1));
    }

    /** @dataProvider knownOperators */
    public function testAcceptsKnownOperators(string $operator): void
    {
        $sql = $this->compile((new SelectQuery())->from('users')->where('id', $operator, 1));
        self::assertStringContainsString('?', $sql);
    }

    public function testRawFragmentsStayExplicit(): void
    {
        $sql = $this->compile((new SelectQuery())->from('users')->where('id', new Fragment('= ANY'), new Fragment('(1)')));
        self::assertStringContainsString('= ANY', $sql);
    }

    private function compile(SelectQuery $query): string
    {
        return (new MySQLCompiler('``'))->compile(new QueryParameters(), '', $query);
    }
}

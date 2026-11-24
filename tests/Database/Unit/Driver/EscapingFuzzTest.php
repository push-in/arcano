<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Unit\Driver;

use Arcano\Database\Driver\CachingCompilerInterface;
use Arcano\Database\Driver\Compiler;
use Arcano\Database\Driver\CompilerCache;
use Arcano\Database\Driver\MySQL\MySQLCompiler;
use Arcano\Database\Driver\Postgres\PostgresCompiler;
use Arcano\Database\Driver\SQLite\SQLiteCompiler;
use Arcano\Database\Driver\SQLServer\SQLServerCompiler;
use Arcano\Database\Exception\BuilderException;
use Arcano\Database\Injection\ParameterInterface;
use Arcano\Database\Query\DeleteQuery;
use Arcano\Database\Query\InsertQuery;
use Arcano\Database\Query\OnConflict;
use Arcano\Database\Query\QueryParameters;
use Arcano\Database\Query\SelectQuery;
use Arcano\Database\Query\UpdateQuery;
use PHPUnit\Framework\TestCase;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;

/**
 * Property test for the query compilers of every dialect.
 *
 * Hostile values (quotes, backslashes, NUL, unicode look-alikes, comment sequences, "; DROP")
 * must always travel as bound parameters, never inside the SQL string. Hostile identifiers must
 * come out as one delimited identifier per dot-separated part, with the closing delimiter
 * doubled and nothing able to break out of the quotes.
 *
 * Reproduce a failure with ARCANO_FUZZ_SEED=<seed from the message>; raise the run length with
 * ARCANO_FUZZ_ITERATIONS.
 */
final class EscapingFuzzTest extends TestCase
{
    private const DEFAULT_SEED = 20261124;
    private const DEFAULT_ITERATIONS = 400;

    private const HOSTILE = [
        "'", '"', '`', '[', ']', '\\', "\0", "\\'", '\\"', "''", '""', ']]', '``',
        '--', '/*', '*/', '#', ';', '; DROP TABLE users; --', "' OR '1'='1", '" OR "1"="1',
        "\n", "\r", "\t", "\x1a", "\x08", '%', '_', '?', ':name', '$1', '@@version',
        'ç', 'ã', 'é', '😀', "\u{2019}", "\u{FF07}", "\u{02BC}", "\u{FEFF}", "\u{200B}",
        "\xC0\xA7", "\xBF\x27", "\xE5\x5C", 'a', 'B', '0', '9', 'user', 'id', 'select',
    ];

    /** Characters that make Quoter::quote() treat a name as a trusted SQL expression (or a path). */
    private const EXPRESSION_MARKERS = [' ', '(', ')', '.', '->'];

    private Randomizer $random;
    private int $seed;

    /**
     * @return iterable<string, array{Compiler, string}>
     */
    public static function dialects(): iterable
    {
        yield 'mysql' => [new MySQLCompiler('``'), '``'];
        yield 'postgres' => [new PostgresCompiler('""'), '""'];
        yield 'sqlite' => [new SQLiteCompiler('""'), '""'];
        yield 'sqlserver' => [new SQLServerCompiler('[]'), '[]'];
    }

    protected function setUp(): void
    {
        $seed = \getenv('ARCANO_FUZZ_SEED');
        $this->seed = $seed !== false && $seed !== '' ? (int) $seed : self::DEFAULT_SEED;
        $this->random = new Randomizer(new Xoshiro256StarStar(\hash('sha256', (string) $this->seed, true)));
    }

    /** @dataProvider dialects */
    public function testQuoteIdentifierNeverBreaksOut(Compiler $compiler, string $quotes): void
    {
        for ($i = 0; $i < $this->iterations(); $i++) {
            $identifier = $this->hostile(withDots: true);
            if ($identifier === '*') {
                continue;
            }

            $quoted = $compiler->quoteIdentifier($identifier);

            $this->assertSame(
                \explode('.', $identifier),
                $this->parseQualified($quoted, $quotes),
                $this->context($i, $identifier, $quoted),
            );
        }
    }

    /** @dataProvider dialects */
    public function testHostileNamesInQueriesAreQuoted(Compiler $compiler, string $quotes): void
    {
        for ($i = 0; $i < $this->iterations(); $i++) {
            $table = $this->hostileName();
            $column = $this->hostileName();
            $value = $this->hostile() . $this->marker($i);

            $queries = [
                'select' => (new SelectQuery())->from($table)->columns($column)->where($column, $value),
                'insert' => (new InsertQuery($table))->columns($column)->values([$value]),
                'update' => (new UpdateQuery($table))->set($column, $value)->where($column, '!=', $value),
                'delete' => (new DeleteQuery($table))->where($column, $value),
            ];

            $quotedTable = $compiler->quoteIdentifier($table);
            $quotedColumn = $compiler->quoteIdentifier($column);

            foreach ($queries as $type => $query) {
                $params = new QueryParameters();
                $sql = $compiler->compile($params, '', $query);
                $context = $this->context($i, "$type table=$table column=$column", $sql);

                $this->assertStringContainsString($quotedTable, $sql, $context);
                $this->assertStringContainsString($quotedColumn, $sql, $context);
                $this->assertSame([$table], $this->parseQualified($quotedTable, $quotes), $context);
                $this->assertSame([$column], $this->parseQualified($quotedColumn, $quotes), $context);

                // Remove the quoted names; the rest of the statement is builder-made SQL only.
                // strtr() matches the longest key first: one quoted name can contain the other.
                $skeleton = \strtr($sql, [$quotedTable => 'T', $quotedColumn => 'C']);
                $this->assertDoesNotMatchRegularExpression('/[\'"`\\\\\x00;#]|--|\/\*/', $skeleton, $context);
                $this->assertValueBound($value, $sql, $params, $context);
            }
        }
    }

    /** @dataProvider dialects */
    public function testHostileValuesAreAlwaysBound(Compiler $compiler): void
    {
        for ($i = 0; $i < $this->iterations(); $i++) {
            $marker = $this->marker($i);
            $values = [];
            for ($n = 0; $n < 8; $n++) {
                $values[] = $this->hostile() . $marker . $n;
            }

            $queries = [
                'select' => (new SelectQuery())
                    ->from('users AS u')
                    ->columns('u.id', 'u.name')
                    ->join('LEFT', 'posts AS p')->on('p.user_id', 'u.id')->onWhere('p.title', $values[0])
                    ->where('u.name', $values[1])
                    ->orWhere('u.email', 'LIKE', $values[2])
                    ->where('u.role', 'IN', new \Arcano\Database\Injection\Parameter([$values[3], $values[4]]))
                    ->where('u.age', 'BETWEEN', $values[5], $values[6])
                    ->groupBy('u.id')
                    ->having('u.name', '!=', $values[7])
                    ->orderBy('u.id')
                    ->limit($this->random->getInt(0, 1000))
                    ->offset($this->random->getInt(0, 1000)),
                'insert' => (new InsertQuery('users'))->columns('name', 'email')
                    ->values([$values[0], $values[1]])
                    ->values([$values[2], $values[3]]),
                'upsert' => (new InsertQuery('users'))->columns('email', 'name')
                    ->values([$values[0], $values[1]])
                    ->onConflict(OnConflict::target('email')->doUpdate(['name' => $values[2]])),
                'update' => (new UpdateQuery('users'))
                    ->values(['name' => $values[0], 'email' => $values[1]])
                    ->where('id', $values[2])
                    ->orWhere('email', 'IN', new \Arcano\Database\Injection\Parameter([$values[3], $values[4]])),
                'delete' => (new DeleteQuery('users'))
                    ->where('name', $values[0])
                    ->where(static function ($q) use ($values): void {
                        $q->where('email', $values[1])->orWhere('email', $values[2]);
                    }),
            ];

            foreach ($queries as $type => $query) {
                $params = new QueryParameters();
                try {
                    $sql = $compiler->compile($params, '', $query);
                } catch (BuilderException) {
                    // A dialect may refuse a statement shape (e.g. an upsert form); refusing is safe.
                    continue;
                }

                $context = $this->context($i, $type, $sql);
                $this->assertStringNotContainsString($marker, $sql, $context);
                $this->assertSame(\substr_count($sql, '?'), \count($params->getParameters()), $context);

                $bound = $this->boundValues($params);
                foreach ($values as $value) {
                    if (\str_contains(\serialize($query->getTokens()), \serialize($value))) {
                        $this->assertContains($value, $bound, $context);
                    }
                }
            }
        }
    }

    /**
     * The statement cache keys on the names; two queries whose names only re-split the same
     * characters must still get their own SQL.
     *
     * @dataProvider dialects
     */
    public function testCacheNeverServesAnotherQuerysSql(Compiler $compiler): void
    {
        if (!$compiler instanceof CachingCompilerInterface) {
            $this->markTestSkipped(\get_class($compiler) . ' is not cached.');
        }

        for ($i = 0; $i < $this->iterations(); $i++) {
            $cache = new CompilerCache($compiler);
            $chars = $this->hostileName() . $this->hostileName();
            $variants = [];
            foreach ([1, \strlen($chars) - 1, \intdiv(\strlen($chars), 2)] as $cut) {
                [$table, $column] = [\trim(\substr($chars, 0, $cut)), \trim(\substr($chars, $cut))];
                if ($table === '' || $column === '' || $table === '*' || $column === '*') {
                    continue;
                }
                $variants[] = (new InsertQuery($table))->columns($column)->values(['v']);
                $variants[] = (new SelectQuery())->from($table)->columns($column)->where($column, 'v');
                $variants[] = (new InsertQuery($table))->columns($column, 'id')->values(['v', 1])
                    ->onConflict(OnConflict::target($column)->doUpdate([$column]));
            }

            foreach ($variants as $n => $query) {
                try {
                    $expected = $compiler->compile($rawParams = new QueryParameters(), '', $query);
                } catch (BuilderException) {
                    continue;
                }
                $params = new QueryParameters();
                $this->assertSame(
                    $expected,
                    $cache->compile($params, '', $query),
                    $this->context($i, "variant $n of " . $chars, $expected),
                );
                $this->assertSame($this->boundValues($rawParams), $this->boundValues($params));
            }
        }
    }

    private function assertValueBound(string $value, string $sql, QueryParameters $params, string $context): void
    {
        $this->assertStringNotContainsString($value, $sql, $context);
        $this->assertContains($value, $this->boundValues($params), $context);
    }

    /**
     * @return list<mixed>
     */
    private function boundValues(QueryParameters $params): array
    {
        return \array_map(
            static fn(mixed $p): mixed => $p instanceof ParameterInterface ? $p->getValue() : $p,
            $params->getParameters(),
        );
    }

    /**
     * Reads a dot-separated chain of delimited identifiers the way the database would and returns
     * the unescaped parts. Fails if anything is left over (a breakout) or a quote is unbalanced.
     *
     * @return list<string>
     */
    private function parseQualified(string $sql, string $quotes): array
    {
        [$left, $right] = [$quotes[0], $quotes[1]];
        $parts = [];
        $pos = 0;
        $length = \strlen($sql);

        while (true) {
            $this->assertLessThan($length, $pos, "Truncated identifier: $sql");
            $this->assertSame($left, $sql[$pos], "Identifier must open with $left: $sql");
            $pos++;
            $part = '';
            while (true) {
                $this->assertLessThan($length, $pos, "Unterminated identifier: $sql");
                $char = $sql[$pos];
                if ($char === $right) {
                    if ($pos + 1 < $length && $sql[$pos + 1] === $right) {
                        $part .= $right;
                        $pos += 2;
                        continue;
                    }
                    $pos++;
                    break;
                }
                $part .= $char;
                $pos++;
            }
            $parts[] = $part;

            if ($pos === $length) {
                return $parts;
            }
            $this->assertSame('.', $sql[$pos], "Text after a closing $right: $sql");
            $pos++;
        }
    }

    private function hostile(bool $withDots = false): string
    {
        $out = '';
        $count = $this->random->getInt(1, 8);
        for ($i = 0; $i < $count; $i++) {
            $out .= self::HOSTILE[$this->random->getInt(0, \count(self::HOSTILE) - 1)];
            if ($withDots && $this->random->getInt(0, 5) === 0) {
                $out .= '.';
            }
        }

        return $out;
    }

    /** A hostile name that Quoter::quote() must treat as a plain identifier, not an expression. */
    private function hostileName(): string
    {
        do {
            $name = $this->hostile();
            foreach (self::EXPRESSION_MARKERS as $marker) {
                $name = \str_replace($marker, '', $name);
            }
            // String names are split on commas and trimmed by the builder (fetchIdentifiers).
            $name = \trim($name);
        } while ($name === '' || $name === '*');

        return $name;
    }

    private function marker(int $i): string
    {
        return \sprintf('Mk%dx%s', $i, \bin2hex($this->random->getBytes(4)));
    }

    private function iterations(): int
    {
        $env = \getenv('ARCANO_FUZZ_ITERATIONS');

        return $env !== false && $env !== '' ? \max(1, (int) $env) : self::DEFAULT_ITERATIONS;
    }

    private function context(int $iteration, string $input, string $sql): string
    {
        return \sprintf(
            'seed=%d iteration=%d input=%s sql=%s',
            $this->seed,
            $iteration,
            \json_encode($input, \JSON_INVALID_UTF8_SUBSTITUTE),
            \json_encode($sql, \JSON_INVALID_UTF8_SUBSTITUTE),
        );
    }
}

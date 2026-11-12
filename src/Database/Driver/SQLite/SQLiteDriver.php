<?php

/**
 * This file is part of Cycle ORM package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Arcano\Database\Driver\SQLite;

use Arcano\Database\Config\DriverConfig;
use Arcano\Database\Config\SQLiteDriverConfig;
use Arcano\Database\Driver\CursorInterface;
use Arcano\Database\Driver\CursorOptions;
use Arcano\Database\Driver\Driver;
use Arcano\Database\Driver\SQLite\Query\SQLiteDeleteQuery;
use Arcano\Database\Driver\SQLite\Query\SQLiteInsertQuery;
use Arcano\Database\Driver\SQLite\Query\SQLiteSelectQuery;
use Arcano\Database\Driver\SQLite\Query\SQLiteUpdateQuery;
use Arcano\Database\Exception\DriverException;
use Arcano\Database\Exception\StatementException;
use Arcano\Database\Query\QueryBuilder;
use Arcano\Database\StatementInterface;

class SQLiteDriver extends Driver implements CursorInterface
{
    /**
     * @param SQLiteDriverConfig $config
     */
    public static function create(DriverConfig $config): static
    {
        return new static(
            $config,
            new SQLiteHandler(),
            new SQLiteCompiler('""'),
            new QueryBuilder(
                new SQLiteSelectQuery(),
                new SQLiteInsertQuery(),
                new SQLiteUpdateQuery(),
                new SQLiteDeleteQuery(),
            ),
        );
    }

    public function getType(): string
    {
        return 'SQLite';
    }

    /**
     * Iterate rows from a SELECT lazily.
     *
     * SQLite has no SQL-level DECLARE CURSOR, but its core engine is already
     * row-oriented: every `PDOStatement::fetch()` advances the prepared
     * statement by exactly one row via `sqlite3_step()`, pulling it from disk
     * without buffering the full result set. The {@see CursorInterface}
     * contract (snapshot consistency for the duration of the enclosing
     * transaction) is therefore satisfied by the engine + an active
     * transaction:
     *
     *  - WAL mode: a reader snapshot is taken on the first read and kept
     *    until COMMIT / ROLLBACK; concurrent writers do not affect it.
     *  - Rollback journal mode: a SHARED lock is held that blocks writers
     *    until the transaction completes.
     *
     * SQLite has no driver-specific cursor options; the base {@see CursorOptions}
     * (`mode` only) is sufficient.
     *
     * @return \Generator<int, array<array-key, mixed>>
     *
     * @throws DriverException When no transaction is active.
     */
    public function cursor(
        string $statement,
        iterable $parameters = [],
        CursorOptions $options = new CursorOptions(),
        int $mode = StatementInterface::FETCH_ASSOC,
    ): \Generator {
        if ($this->getTransactionLevel() === 0) {
            throw new DriverException(
                'SQLite cursor requires an active transaction to guarantee snapshot consistency. '
                . 'Wrap the cursor iteration in Database::transaction() or call beginTransaction() before cursor().',
            );
        }

        $sqliteStatement = $this->statement($statement, $parameters);

        try {
            while (($row = $sqliteStatement->fetch($mode)) !== false) {
                yield $row;
            }
        } finally {
            $sqliteStatement->close();
        }
    }

    /**
     * Whether `$column` is the rowid alias of `$table` (its single INTEGER PRIMARY KEY), so the
     * last insert id is the inserted key. Read from the schema without query logging.
     *
     * @internal
     */
    public function isRowidAlias(string $table, string $column): bool
    {
        try {
            $pdo = $this->getPDO();
            $statement = $pdo->prepare("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = ?");
            $statement->execute([$table]);
            $definition = $statement->fetchColumn();
            if (!\is_string($definition) || \preg_match('/without\s+rowid|primary\s+key\s+desc/i', $definition) === 1) {
                return false;
            }
            $keys = [];
            foreach ($pdo->query('PRAGMA table_info(' . $this->identifier($table) . ')')->fetchAll(\PDO::FETCH_ASSOC) as $info) {
                (int) $info['pk'] > 0 and $keys[] = $info;
            }
        } catch (\Throwable) {
            return false;
        }

        return \count($keys) === 1
            && $keys[0]['name'] === $column
            && \strtoupper((string) $keys[0]['type']) === 'INTEGER';
    }

    protected function mapException(\Throwable $exception, string $query): StatementException
    {
        if (\str_starts_with(self::getSqlState($exception) ?? '', '23')) {
            return new StatementException\ConstrainException($exception, $query);
        }

        return new StatementException($exception, $query);
    }

    protected function setIsolationLevel(string $level): void
    {
        $this->logger?->alert("Transaction isolation level is not fully supported by SQLite ({$level})");
    }
}

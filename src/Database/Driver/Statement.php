<?php

/**
 * This file is part of Cycle ORM package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Arcano\Database\Driver;

use PDOStatement;
use Arcano\Database\StatementInterface;

/**
 * Adds few quick methods to PDOStatement and fully compatible with it. By default uses
 * PDO::FETCH_ASSOC mode.
 *
 * @internal Do not use this class directly.
 */
final class Statement implements StatementInterface, \IteratorAggregate
{
    /**
     * The latest wrapper of each live prepared statement, by object id. Drivers cache
     * prepared statements, so an earlier wrapper must not close the cursor of a newer
     * execution. A reused object id is overwritten by its first wrapper.
     *
     * @var array<int, int>
     */
    private static array $owners = [];

    private static int $sequence = 0;

    private readonly int $id;

    /** False once the rows are read or the cursor is closed. */
    private bool $open = true;

    public function __construct(
        private \PDOStatement|PDOStatementInterface $pdoStatement,
    ) {
        $this->pdoStatement->setFetchMode(self::FETCH_ASSOC);
        self::$owners[\spl_object_id($pdoStatement)] = $this->id = ++self::$sequence;
    }

    /**
     * A cached statement left with unread rows keeps its cursor, which holds the read
     * lock of a SQLite file and server resources until the statement runs again.
     */
    public function __destruct()
    {
        if ($this->open && self::$owners[\spl_object_id($this->pdoStatement)] === $this->id) {
            $this->pdoStatement->closeCursor();
        }
    }

    public function getQueryString(): string
    {
        return $this->pdoStatement->queryString;
    }

    public function getPDOStatement(): \PDOStatement|PDOStatementInterface
    {
        return $this->pdoStatement;
    }

    public function fetch(int $mode = self::FETCH_ASSOC): mixed
    {
        return $this->pdoStatement->fetch($mode);
    }

    public function fetchColumn(?int $columnNumber = null): mixed
    {
        return $columnNumber === null
            ? $this->pdoStatement->fetchColumn()
            : $this->pdoStatement->fetchColumn($columnNumber);
    }

    public function fetchAll(int $mode = self::FETCH_ASSOC): array
    {
        $rows = $this->pdoStatement->fetchAll($mode);
        $this->open = false;
        return $rows;
    }

    public function rowCount(): int
    {
        return $this->pdoStatement->rowCount();
    }

    public function columnCount(): int
    {
        return $this->pdoStatement->columnCount();
    }

    public function getIterator(): \Generator
    {
        foreach ($this->pdoStatement as $row) {
            yield $row;
        }

        $this->close();
    }

    public function close(): void
    {
        $this->pdoStatement->closeCursor();
        $this->open = false;
    }

    /**
     * Rows affected by a statement without a result set, which leaves no cursor to close.
     *
     * @internal
     */
    public function affectedRows(): int
    {
        $this->open = $this->pdoStatement->columnCount() > 0;
        return $this->pdoStatement->rowCount();
    }
}

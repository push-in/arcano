<?php

declare(strict_types=1);

namespace Arcano\ORM\Command\Database;

use Arcano\Database\Database;
use Arcano\Database\DatabaseInterface;
use Arcano\Database\Driver\SQLite\SQLiteDriver;
use Arcano\Database\Injection\Parameter;
use Arcano\Database\Query\InsertQuery;
use Arcano\Database\Query\QueryParameters;
use Arcano\Database\Query\ReturningInterface;

/**
 * Statements compiled once per table and column set, so repeated writes skip
 * the query builder and the compiler. Only scalar and null values qualify.
 *
 * @internal
 */
final class StatementPlans
{
    private const MAX_PLANS = 1024;

    /** @var \WeakMap<DatabaseInterface, \ArrayObject<string, array|false>>|null */
    private static ?\WeakMap $plans = null;

    /**
     * Runs `UPDATE table SET values WHERE where` and returns the affected rows,
     * or null when the values need the query builder.
     */
    public static function update(DatabaseInterface $db, string $table, array $values, array $where): ?int
    {
        if (!$db instanceof Database) {
            return null;
        }
        $key = $table . "\0";
        $args = [];
        foreach ($values as $column => $value) {
            if ($value === null) {
                $key .= "{$column}\0n\1";
                continue;
            }
            if (!\is_scalar($value)) {
                return null;
            }
            $key .= "{$column}\1";
            $args[] = $value;
        }
        $key .= "\2";
        foreach ($where as $column => $value) {
            if (!\is_scalar($value)) {
                return null;
            }
            $key .= "{$column}\1";
            $args[] = $value;
        }

        self::$plans ??= new \WeakMap();
        $plans = self::$plans[$db] ??= new \ArrayObject();
        $plan = $plans[$key] ?? null;
        if ($plan === null) {
            if (\count($plans) >= self::MAX_PLANS) {
                return null;
            }
            $plan = $plans[$key] = self::compileUpdate($db, $table, $values, $where);
        }
        if ($plan === false) {
            return null;
        }

        [$driver, $sql, $parameters, $slots] = $plan;
        foreach ($slots as $i => $index) {
            $parameters[$index] = new Parameter($args[$i]);
        }

        return $driver->execute($sql, $parameters);
    }

    /** @var \WeakMap<DatabaseInterface, bool>|null Whether the insert queries of a database support RETURNING. */
    private static ?\WeakMap $returning = null;

    /**
     * Whether `$db->insert()` builds queries with RETURNING support (it depends on the driver only).
     */
    public static function returns(DatabaseInterface $db, string $table): bool
    {
        self::$returning ??= new \WeakMap();
        return self::$returning[$db] ??= $db->insert($table) instanceof ReturningInterface;
    }

    /**
     * Runs `INSERT INTO table values RETURNING returning` through a cached statement and
     * returns its result as {@see InsertQuery::run()} does, or false in the first slot when
     * the values need the query builder.
     *
     * @return array{bool, mixed}
     */
    public static function insert(DatabaseInterface $db, string $table, array $values, array $returning): array
    {
        if (!$db instanceof Database) {
            return [false, null];
        }
        $key = "i\0{$table}\0" . \implode("\0", $returning) . "\2";
        $args = [];
        foreach ($values as $column => $value) {
            if ($value === null) {
                $key .= "{$column}\0n\1";
                continue;
            }
            if (!\is_scalar($value)) {
                return [false, null];
            }
            $key .= "{$column}\1";
            $args[] = $value;
        }

        self::$plans ??= new \WeakMap();
        $plans = self::$plans[$db] ??= new \ArrayObject();
        $plan = $plans[$key] ?? null;
        if ($plan === null) {
            if (\count($plans) >= self::MAX_PLANS) {
                return [false, null];
            }
            $plan = $plans[$key] = self::compileInsert($db, $table, $values, $returning);
        }
        if ($plan === false) {
            return [false, null];
        }

        [$query, $sql, $parameters, $slots] = $plan;
        foreach ($slots as $i => $index) {
            $parameters[$index] = new Parameter($args[$i]);
        }

        return [true, $query->runStatement($sql, $parameters)];
    }

    private static function compileInsert(Database $db, string $table, array $values, array $returning): array|false
    {
        $sentinels = [];
        foreach ($values as $column => $value) {
            if ($value !== null) {
                $values[$column] = $sentinels[] = "\0arcano-value-" . \count($sentinels) . "\0";
            }
        }
        $query = $db->insert($table)->values($values);
        if ($returning !== [] && !self::isRowid($db, $table, $returning)) {
            if (!$query instanceof ReturningInterface) {
                return false;
            }
            $query->returning(...$returning);
        }
        $parameters = new QueryParameters();
        $sql = $query->sqlStatement($parameters);

        $slots = self::findSlots($parameters->getParameters(), $sentinels);
        return $slots === null ? false : [$query, $sql, $parameters->getParameters(), $slots];
    }

    /**
     * Whether `$returning` is the rowid alias of an SQLite table (a single INTEGER PRIMARY KEY):
     * then the last insert id is the returned value and RETURNING, which costs a result set
     * per row, is not needed.
     */
    private static function isRowid(Database $db, string $table, array $returning): bool
    {
        $driver = $db->getDriver();
        if (!$driver instanceof SQLiteDriver || \count($returning) !== 1 || \str_contains($table, '.')) {
            return false;
        }

        return $driver->isRowidAlias($db->getPrefix() . $table, $returning[0]);
    }

    /**
     * Statement parameter index of each sentinel, or null when one is missing,
     * repeated or not a plain parameter.
     *
     * @return array<int, int>|null
     */
    private static function findSlots(array $parameters, array $sentinels): ?array
    {
        $positions = \array_flip($sentinels);
        $slots = [];
        foreach ($parameters as $index => $parameter) {
            $raw = $parameter instanceof Parameter ? $parameter->getValue() : $parameter;
            if (!\is_string($raw) || !\str_contains($raw, "\0arcano-value-")) {
                continue;
            }
            $slot = $positions[$raw] ?? null;
            if ($slot === null || isset($slots[$slot]) || !$parameter instanceof Parameter) {
                return null;
            }
            $slots[$slot] = $index;
        }

        return \count($slots) === \count($sentinels) ? $slots : null;
    }

    private static function compileUpdate(DatabaseInterface $db, string $table, array $values, array $where): array|false
    {
        $sentinels = [];
        foreach ($values as $column => $value) {
            if ($value !== null) {
                $values[$column] = $sentinels[] = "\0arcano-value-" . \count($sentinels) . "\0";
            }
        }
        foreach ($where as $column => $value) {
            $where[$column] = $sentinels[] = "\0arcano-value-" . \count($sentinels) . "\0";
        }

        $query = $db->update($table, $values, $where);
        $parameters = new QueryParameters();
        $sql = $query->sqlStatement($parameters);

        $slots = self::findSlots($parameters->getParameters(), $sentinels);
        return $slots === null ? false : [$query->getDriver(), $sql, $parameters->getParameters(), $slots];
    }
}

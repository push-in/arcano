<?php

declare(strict_types=1);

namespace Arcano\ORM\Command\Database;

use Arcano\Database\Database;
use Arcano\Database\DatabaseInterface;
use Arcano\Database\Injection\Parameter;
use Arcano\Database\Query\QueryParameters;

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
        $parameters = $parameters->getParameters();

        $positions = \array_flip($sentinels);
        $slots = [];
        foreach ($parameters as $index => $parameter) {
            $raw = $parameter instanceof Parameter ? $parameter->getValue() : $parameter;
            if (!\is_string($raw) || !\str_contains($raw, "\0arcano-value-")) {
                continue;
            }
            $slot = $positions[$raw] ?? null;
            if ($slot === null || isset($slots[$slot]) || !$parameter instanceof Parameter) {
                return false;
            }
            $slots[$slot] = $index;
        }

        return \count($slots) === \count($sentinels) ? [$query->getDriver(), $sql, $parameters, $slots] : false;
    }
}

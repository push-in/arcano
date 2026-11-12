<?php

// phpcs:ignoreFile
declare(strict_types=1);

namespace Arcano\ORM\Tests\Fixtures;

use Arcano\Database\DatabaseInterface;

class StaticCallableRule
{
    public static function invoke(string $value, DatabaseInterface $database, mixed $argument): array
    {
        return [
            'value' => $value,
            'database' => $database,
            'arguments' => [$argument],
        ];
    }

    public static function invokeVariadic(string $value, DatabaseInterface $database, mixed ...$arguments): array
    {
        return [
            'value' => $value,
            'database' => $database,
            'arguments' => $arguments,
        ];
    }

    public static function invokeWithoutDatabaseVariadic(string $value, mixed ...$arguments): array
    {
        return [
            'value' => $value,
            'arguments' => $arguments,
        ];
    }

    public static function invokeWithoutDatabase(string $value, mixed $argument): array
    {
        return [
            'value' => $value,
            'arguments' => [$argument],
        ];
    }
}

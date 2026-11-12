<?php

/**
 * This file is part of Cycle ORM package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Arcano\Database\Driver\SQLite\Query\Traits;

use Arcano\Database\Driver\SQLite\Injection\CompileJson;
use Arcano\Database\Driver\SQLite\Injection\CompileJsonContainsKey;
use Arcano\Database\Driver\SQLite\Injection\CompileJsonDoesntContainKey;
use Arcano\Database\Driver\SQLite\Injection\CompileJsonLength;
use Arcano\Database\Exception\BuilderException;
use Arcano\Database\Exception\DriverException;

/**
 * @internal
 *
 * @psalm-internal Arcano\Database\Driver\SQLite
 */
trait WhereJsonTrait
{
    /**
     * @param non-empty-string $column
     * @param non-empty-string $method
     * @param array<non-empty-string, mixed> $params
     */
    protected function buildJsonInjection(
        string $column,
        mixed $value,
        string $method,
        array $params,
    ): array {
        return match ($method) {
            'whereJson', 'orWhereJson' => [new CompileJson($column), $value],
            'whereJsonContains', 'orWhereJsonContains', 'whereJsonDoesntContain',
            'orWhereJsonDoesntContain' => throw new DriverException(
                'This SQLite does not support JSON contains operations.',
            ),
            'whereJsonContainsKey', 'orWhereJsonContainsKey' => [new CompileJsonContainsKey($column)],
            'whereJsonDoesntContainKey', 'orWhereJsonDoesntContainKey' => [new CompileJsonDoesntContainKey($column)],
            'whereJsonLength', 'orWhereJsonLength' => [new CompileJsonLength($column, $value, $params['operator'])],
            default => null,
        } ?? throw new BuilderException("This database engine can't handle the `$method` method.");
    }
}

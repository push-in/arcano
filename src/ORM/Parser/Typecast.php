<?php

declare(strict_types=1);

namespace Arcano\ORM\Parser;

use BackedEnum;
use Arcano\ORM\Exception\TypecastException;
use Arcano\Database\DatabaseInterface;

/**
 * Default typecasting class for ORM entities.
 *
 * This class handles casting data from database format to PHP types and vice versa.
 *
 * It supports various rule types including:
 *  - Built-in primitives: 'int', 'bool', 'float', 'datetime'.
 *  - JSON encoding/decoding: 'json'.
 *  - Backed enums: Any class implementing BackedEnum
 *  - Callable functions:
 *      - Single-argument static factory: [ClassName::class, 'simple'].
 *      - Callable with database instance: [ClassName::class, 'withDatabase']
 *      - Callable with additional arguments: [ClassName::class, 'customArgs', ['value1', 'value2']].
 *
 *      ```
 *      class ClassName {
 *          public static function simple(mixed $value): mixed { ... }
 *
 *          public static function withDatabase(
 *              mixed $value,
 *              DatabaseInterface $database, // Will be injected automatically
 *          ): mixed { ... }
 *
 *          public static function customArgs(
 *              mixed $value,
 *              string $arg1, // 'value1' will be passed
 *              string $arg2, // 'value2' will be passed
 *          ): mixed { ... }
 *      }
 *      ```
 */
final class Typecast implements CastableInterface, UncastableInterface
{
    private const RULES = ['int', 'bool', 'float', 'datetime', 'json'];

    /** @var array<non-empty-string, \Closure(mixed): mixed> */
    private array $casters = [];

    /** @var array<non-empty-string, \Closure(mixed): mixed> */
    private array $uncasters = [];

    /**
     * Rules that compiled makers can inline: a plain PHP cast, or the caster of a datetime.
     *
     * @var array<non-empty-string, 'int'|'bool'|'float'|\Closure(mixed): mixed>
     */
    private array $plain = [];

    /** @var array<non-empty-string, true> Fields cast by the datetime rule. */
    private array $datetimes = [];

    private ?\DateTimeZone $timezone = null;

    /**
     * @param non-empty-string $role The role of the entity being typecasted
     * @param DatabaseInterface $database The database instance used for typecasting
     */
    public function __construct(
        private string $role,
        private DatabaseInterface $database,
    ) {}

    public function setRules(array $rules): array
    {
        foreach ($rules as $key => $rule) {
            unset($this->datetimes[$key]);
            // Static rules
            if (\in_array($rule, self::RULES, true)) {
                $this->casters[$key] = match ($rule) {
                    'int' => static fn(mixed $value): int => (int) $value,
                    'bool' => static fn(mixed $value): bool => (bool) $value,
                    'float' => static fn(mixed $value): float => (float) $value,
                    // The driver timezone is fixed per connection: resolved once, not per value.
                    'datetime' => fn (mixed $value): \DateTimeImmutable => new \DateTimeImmutable($value, $this->timezone()),
                    // Mostly an array, but can also be a scalar type.
                    'json' => static fn(mixed $value): mixed => \json_decode(
                        $value,
                        true,
                        512,
                        \JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE,
                    ),
                };

                if ($rule === 'int' || $rule === 'bool' || $rule === 'float') {
                    $this->plain[$key] = $rule;
                } elseif ($rule === 'datetime') {
                    // Database timestamps always parse: compiled makers construct them inline
                    // in the driver timezone (see plainRules()).
                    $this->plain[$key] = $this->casters[$key];
                    $this->datetimes[$key] = true;
                } else {
                    unset($this->plain[$key]);
                }

                if ($rule === 'json') {
                    $this->uncasters[$key] = static fn(mixed $value): string => \json_encode(
                        $value,
                        \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE,
                    );
                }
                unset($rules[$key]);
                continue;
            }

            // Backed enum rules
            if (\is_string($rule) && \is_subclass_of($rule, \BackedEnum::class, true)) {
                /** @var class-string<\BackedEnum> $rule */
                $reflection = new \ReflectionEnum($rule);
                $type = (string) $reflection->getBackingType();

                $this->casters[$key] = $type === 'string'
                    // String backed enum
                    ? static fn(mixed $value): ?\BackedEnum => \is_string($value) || \is_numeric($value)
                        ? $rule::tryFrom((string) $value)
                        : null
                    // Int backed enum
                    : static fn(mixed $value): ?\BackedEnum => \is_int($value) || \is_string($value) && \preg_match('/^\\d++$/', $value) === 1
                        ? $rule::tryFrom((int) $value)
                        : null;

                $this->uncasters[$key] = static fn(mixed $value): mixed => $value instanceof \BackedEnum
                    ? $value->value
                    : $value;

                unset($rules[$key]);
                continue;
            }

            // Callable rules
            if (\is_callable($rule)) {
                $closure = \Closure::fromCallable($rule);
                $this->casters[$key] = (new \ReflectionFunction($closure))->getNumberOfParameters() === 1
                    ? $closure
                    : fn(mixed $value): mixed => $closure($value, $this->database);
                unset($rules[$key]);
                continue;
            }

            // Callable rules with arguments
            if (\is_array($rule) && \array_keys($rule) === [0, 1, 2] && \is_callable([$rule[0], $rule[1]])) {
                $closure = \Closure::fromCallable([$rule[0], $rule[1]]);
                $args = $rule[2];
                \is_array($args) or throw new \InvalidArgumentException(
                    "The third argument of typecast rule for the `{$this->role}.{$key}` must be an array of arguments.",
                );
                unset($rules[$key]);

                // The callable accepts DatabaseInterface as second argument
                $reflection = new \ReflectionFunction($closure);
                if ($reflection->getParameters()[1]?->getType()->getName() === DatabaseInterface::class) {
                    $this->casters[$key] = fn(mixed $value): mixed => $closure($value, $this->database, ...$args);
                    continue;
                }

                $this->casters[$key] = static fn(mixed $value): mixed => $closure($value, ...$args);
            }
        }

        return $rules;
    }

    /**
     * The rules as plain casts (`int`, `bool`, `float`) or datetime casters when every
     * rule is one, so callers can compile them; null otherwise.
     *
     * @internal
     *
     * @return array<non-empty-string, 'int'|'bool'|'float'|\Closure(mixed): mixed>|null
     */
    public function plainRules(): ?array
    {
        if (\count($this->plain) !== \count($this->casters)) {
            return null;
        }
        $rules = $this->plain;
        foreach ($this->datetimes as $key => $_) {
            // A datetime field: the zone its values are constructed in.
            $rules[$key] = $this->timezone();
        }

        return $rules;
    }

    /**
     * The driver timezone is fixed per connection: resolved once, not per value.
     */
    private function timezone(): \DateTimeZone
    {
        return $this->timezone ??= $this->database->getDriver()->getTimezone();
    }

    public function cast(array $data): array
    {
        try {
            foreach ($this->casters as $key => $callable) {
                if (isset($data[$key])) {
                    $data[$key] = $callable($data[$key]);
                }
            }
        } catch (\Throwable $e) {
            throw new TypecastException(
                \sprintf('Unable to typecast the `%s.%s` field: %s', $this->role, $key, $e->getMessage()),
                $e->getCode(),
                $e,
            );
        }

        return $data;
    }

    /**
     * @throws \JsonException
     */
    public function uncast(array $data): array
    {
        try {
            foreach ($this->uncasters as $key => $callable) {
                if (isset($data[$key])) {
                    $data[$key] = $callable($data[$key]);
                }
            }
        } catch (\Throwable $e) {
            throw new TypecastException(
                "Unable to uncast the `{$this->role}.{$key}` field: {$e->getMessage()}",
                $e->getCode(),
                $e,
            );
        }

        return $data;
    }
}

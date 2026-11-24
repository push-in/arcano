<?php

/**
 * This file is part of Cycle ORM package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Arcano\Database\Driver;

use Arcano\Database\Injection\Expression;
use Arcano\Database\Injection\Fragment;
use Arcano\Database\Injection\FragmentInterface;
use Arcano\Database\Injection\JsonExpression;
use Arcano\Database\Injection\Parameter;
use Arcano\Database\Injection\ParameterInterface;
use Arcano\Database\Injection\SubQuery;
use Arcano\Database\Query\OnConflict;
use Arcano\Database\Query\OnConflictWithPredicate;
use Arcano\Database\Query\QueryInterface;
use Arcano\Database\Query\QueryParameters;
use Arcano\Database\Query\SelectQuery;

/**
 * Caches calculated queries. Code in this class is performance optimized.
 */
final class CompilerCache implements CompilerInterface
{
    /** Compiled statements kept per compiler; the oldest is dropped past this. */
    public const LIMIT = 2048;

    private array $cache = [];

    public function __construct(
        private CachingCompilerInterface $compiler,
    ) {}

    /**
     * @psalm-param non-empty-string $identifier
     */
    public function quoteIdentifier(string $identifier): string
    {
        return $this->compiler->quoteIdentifier($identifier);
    }

    /**
     * @psalm-return non-empty-string
     */
    public function compile(QueryParameters $params, string $prefix, FragmentInterface $fragment): string
    {
        if ($fragment->getType() === self::SELECT_QUERY) {
            $queryHash = self::k($prefix) . $this->hashSelectQuery($params, $fragment->getTokens());

            if (isset($this->cache[$queryHash])) {
                return $this->cache[$queryHash];
            }

            return $this->store($queryHash, $this->compiler->compile(
                new QueryParameters(),
                $prefix,
                $fragment,
            ));
        }

        if ($fragment->getType() === self::INSERT_QUERY) {
            $tokens = $fragment->getTokens();

            if (\count($tokens['values']) === 1) {
                $queryHash = self::k($prefix) . $this->hashInsertQuery($params, $tokens);
                if (isset($this->cache[$queryHash])) {
                    return $this->cache[$queryHash];
                }

                return $this->store($queryHash, $this->compiler->compile(
                    new QueryParameters(),
                    $prefix,
                    $fragment,
                ));
            }
        }

        if ($fragment->getType() === self::UPSERT_QUERY) {
            $tokens = $fragment->getTokens();

            if (\count($tokens['values']) === 1) {
                $queryHash = self::k($prefix) . $this->hashUpsertQuery($params, $tokens);
                if (isset($this->cache[$queryHash])) {
                    return $this->cache[$queryHash];
                }

                return $this->store($queryHash, $this->compiler->compile(
                    new QueryParameters(),
                    $prefix,
                    $fragment,
                ));
            }
        }

        return $this->compiler->compile(
            $params,
            $prefix,
            $fragment,
        );
    }

    /**
     * @psalm-return non-empty-string
     */
    protected function hashInsertQuery(QueryParameters $params, array $tokens): string
    {
        foreach ((array) ($tokens['return'] ?? []) as $return) {
            if ($return instanceof FragmentInterface) {
                foreach ($return->getTokens()['parameters'] as $param) {
                    $params->push($param);
                }
            }
        }

        $hash = 'i_' . self::k($tokens['table']) . 'c';
        foreach ($tokens['columns'] as $column) {
            $hash .= self::k($column);
        }
        $hash .= 'r';
        foreach ((array) ($tokens['return'] ?? []) as $return) {
            $hash .= self::k($return);
        }
        $hash .= 'v';
        foreach ($tokens['values'] as $value) {
            if ($value instanceof FragmentInterface) {
                if ($value instanceof Expression || $value instanceof Fragment) {
                    foreach ($tokens['parameters'] as $param) {
                        $params->push($param);
                    }
                }

                $hash .= 'F' . self::k($value);
                continue;
            }

            if (!$value instanceof ParameterInterface) {
                $value = new Parameter($value);
            }

            if ($value->isArray()) {
                foreach ($value->getValue() as $child) {
                    if ($child instanceof FragmentInterface) {
                        if ($child instanceof \Stringable) {
                            $hash .= '_F_' . self::k($child);
                        }
                        continue;
                    }

                    if (!$child instanceof ParameterInterface) {
                        $child = new Parameter($child);
                    }

                    $params->push($child);
                }

                $hash .= 'P?';
                continue;
            }

            $params->push($value);
            $hash .= 'P?';
        }

        return $hash;
    }

    /**
     * @psalm-return non-empty-string
     */
    protected function hashUpsertQuery(QueryParameters $params, array $tokens): string
    {
        $hash = 'u_' . $this->hashInsertQuery($params, $tokens);

        $onConflict = $tokens['onConflict'] ?? null;
        if (!$onConflict instanceof OnConflict) {
            return $hash;
        }

        // The index-inference predicate is rendered between the conflict target and
        // DO UPDATE, so its parameters must be pushed before the update parameters.
        // Reuse the regular where-hasher to keep that order identical to Compiler::where().
        if ($onConflict instanceof OnConflictWithPredicate && ($predicate = $onConflict->getIndexPredicate()) !== []) {
            $hash .= '_w' . $this->hashWhere($params, $predicate);
        }

        // Driver-specific subclasses extend getCacheKey() to append their own fields
        // and push any embedded fragment parameters via $params.
        return $hash . '_oc' . $onConflict->getCacheKey($params);
    }

    /**
     * @psalm-return non-empty-string
     */
    protected function hashSelectQuery(QueryParameters $params, array $tokens): string
    {
        // stable part of hash
        if (\is_array($tokens['distinct']) && isset($tokens['distinct']['on'])) {
            $hash = 's_' . self::k($tokens['forUpdate']) . '_on_' . self::k($tokens['distinct']['on']);
        } else {
            $hash = 's_' . self::k($tokens['forUpdate']) . '_' . self::k($tokens['distinct']);
        }
        $hash .= 'f';

        foreach ($tokens['from'] as $table) {
            if ($table instanceof SelectQuery) {
                $hash .= 's_' . self::k($table->getPrefix() ?? '');
                $hash .= $this->hashSelectQuery($params, $table->getTokens());
                continue;
            } elseif ($table instanceof SubQuery) {
                $hash .= 'sb_';
                $hash .= $this->hashSelectQuery($params, $table->getTokens());
                continue;
            }

            $hash .= self::k($table);
        }

        $hash .= 'c' . $this->hashColumns($params, $tokens['columns']);

        foreach ($tokens['join'] as $join) {
            $hash .= 'j' . self::k($join['alias']) . self::k(\str_replace(['JOIN', ' '], '', $join['type']));

            if ($join['outer'] instanceof SelectQuery) {
                $hash .= $join['outer']->getPrefix() === null ? '' : 'p_' . self::k($join['outer']->getPrefix());
                $hash .= $this->hashSelectQuery($params, $join['outer']->getTokens());
            } else {
                $hash .= self::k($join['outer']);
            }

            $hash .= 'on' . $this->hashWhere($params, $join['on']);
        }

        if ($tokens['where'] !== []) {
            $hash .= 'w' . $this->hashWhere($params, $tokens['where']);
        }

        if ($tokens['having'] !== []) {
            $hash .= 'h' . $this->hashWhere($params, $tokens['having']);
        }

        $hash .= 'g';
        foreach ($tokens['groupBy'] as $group) {
            $hash .= self::k($group);
        }

        $hash .= 'o' . $this->hashOrderBy($params, $tokens['orderBy']);

        $hash .= 'l' . self::k($this->compiler->hashLimit($params, $tokens));

        foreach ($tokens['union'] as $union) {
            $hash .= 'U' . self::k($union[0]);
            if ($union[1] instanceof SelectQuery) {
                $hash .= $union[1]->getPrefix() === null ? '' : 'p_' . self::k($union[1]->getPrefix());
                $hash .= $this->hashSelectQuery($params, $union[1]->getTokens());
                continue;
            }

            $hash .= self::k($union[1]);
        }

        foreach ($tokens['intersect'] as $intersect) {
            $hash .= 'I' . self::k($intersect[0]);
            if ($intersect[1] instanceof SelectQuery) {
                $hash .= $intersect[1]->getPrefix() === null ? '' : 'i_' . self::k($intersect[1]->getPrefix());
                $hash .= $this->hashSelectQuery($params, $intersect[1]->getTokens());
                continue;
            }

            $hash .= self::k($intersect[1]);
        }

        foreach ($tokens['except'] as $except) {
            $hash .= 'E' . self::k($except[0]);
            if ($except[1] instanceof SelectQuery) {
                $hash .= $except[1]->getPrefix() === null ? '' : 'e_' . self::k($except[1]->getPrefix());
                $hash .= $this->hashSelectQuery($params, $except[1]->getTokens());
                continue;
            }

            $hash .= self::k($except[1]);
        }

        return $hash;
    }

    /**
     * @psalm-return non-empty-string
     */
    protected function hashWhere(QueryParameters $params, array $where): string
    {
        $hash = '';
        foreach ($where as $condition) {
            // OR/AND keyword
            [$boolean, $context] = $condition;

            $hash .= self::k($boolean);
            if (\is_string($context)) {
                $hash .= 'S' . self::k($context);
                continue;
            }

            if ($context instanceof FragmentInterface) {
                if (
                    $context instanceof Expression ||
                    $context instanceof Fragment ||
                    $context instanceof JsonExpression
                ) {
                    foreach ($context->getTokens()['parameters'] as $param) {
                        $params->push($param);
                    }
                }

                $hash .= 'F' . self::k($context);
                continue;
            }

            if ($context[0] instanceof QueryInterface) {
                $hash .= $context[0]->getPrefix() === null ? '' : 'p_' . self::k($context[0]->getPrefix());
                $hash .= $this->hashSelectQuery($params, $context[0]->getTokens());
            } elseif ($context[0] instanceof ParameterInterface) {
                $hash .= $this->hashParam($params, $context[0]);
            } else {
                if ($context[0] instanceof Expression || $context[0] instanceof Fragment) {
                    foreach ($context[0]->getTokens()['parameters'] as $param) {
                        $params->push($param);
                    }
                }

                $hash .= self::k($context[0]);
            }

            // operator
            if ($context[1] instanceof Expression || $context[1] instanceof Fragment) {
                foreach ($context[1]->getTokens()['parameters'] as $param) {
                    $params->push($param);
                }
            }

            $hash .= self::k($context[1]);

            if ($context[2] instanceof QueryInterface) {
                $hash .= $context[2]->getPrefix() === null ? '' : 'p_' . self::k($context[2]->getPrefix());
                $hash .= $this->hashSelectQuery($params, $context[2]->getTokens());
            } elseif ($context[2] instanceof ParameterInterface) {
                $hash .= $this->hashParam($params, $context[2]);
            } else {
                if ($context[2] instanceof Expression || $context[2] instanceof Fragment) {
                    foreach ($context[2]->getTokens()['parameters'] as $param) {
                        $params->push($param);
                    }
                }

                $hash .= self::k($context[2]);
            }

            if (isset($context[3])) {
                if ($context[3] instanceof QueryInterface) {
                    $hash .= $context[3]->getPrefix() === null ? '' : 'p_' . self::k($context[3]->getPrefix());
                    $hash .= $this->hashSelectQuery($params, $context[3]->getTokens());
                } elseif ($context[3] instanceof ParameterInterface) {
                    $hash .= $this->hashParam($params, $context[3]);
                } else {
                    if ($context[3] instanceof Expression || $context[3] instanceof Fragment) {
                        foreach ($context[3]->getTokens()['parameters'] as $param) {
                            $params->push($param);
                        }
                    }

                    $hash .= self::k($context[3]);
                }
            }
        }

        return $hash;
    }

    /**
     * @psalm-return non-empty-string
     */
    protected function hashColumns(QueryParameters $params, array $columns): string
    {
        $hash = '';
        foreach ($columns as $column) {
            if ($column instanceof SelectQuery) {
                // A scalar subquery column: its parameters, not its interpolated values.
                $hash .= 'sq_' . self::k($column->getPrefix() ?? '') . '(' . $this->hashSelectQuery($params, $column->getTokens()) . '),';
                continue;
            }
            if ($column instanceof Expression || $column instanceof Fragment || $column instanceof SubQuery) {
                foreach ($column->getTokens()['parameters'] as $param) {
                    $params->push($param);
                }
            }

            $hash .= self::k($column);
        }

        return $hash;
    }

    /**
     * @psalm-return non-empty-string
     */
    private function hashParam(QueryParameters $params, ParameterInterface $param): string
    {
        if ($param->isNull()) {
            return 'N';
        }

        if ($param->isArray()) {
            $simpleParams = [];
            foreach ($param->getValue() as $value) {
                if ($value instanceof FragmentInterface) {
                    foreach ($value->getTokens()['parameters'] as $fragmentParam) {
                        $params->push($fragmentParam);
                    }
                } else {
                    $simpleParams[] = $value;
                }
            }

            if ($simpleParams !== []) {
                $params->push(new Parameter($simpleParams));
            }

            return 'A' . \count($param->getValue()) . '_';
        }

        $params->push($param);

        return '?';
    }

    private function hashOrderBy(QueryParameters $params, array $tokens): string
    {
        $hash = '';
        foreach ($tokens as $order) {
            if ($order[0] instanceof FragmentInterface) {
                foreach ($order[0]->getTokens()['parameters'] as $param) {
                    $params->push($param);
                }
            }
            $hash .= self::k($order[0]) . self::k($order[1]);
        }

        return $hash;
    }

    /**
     * Length-prefixed piece of a cache key. Names and fragments are concatenated into one key, so
     * without the length "ta" + "b" and "t" + "ab" (table + column) would share a compiled statement.
     */
    private static function k(mixed $piece): string
    {
        $piece = (string) $piece;

        return \strlen($piece) . ':' . $piece;
    }

    /**
     * @param non-empty-string $sql
     * @return non-empty-string
     */
    private function store(string $hash, string $sql): string
    {
        if (\count($this->cache) >= self::LIMIT) {
            unset($this->cache[\array_key_first($this->cache)]);
        }
        return $this->cache[$hash] = $sql;
    }
}

<?php

declare(strict_types=1);

namespace Arcano\ORM;

use Arcano\Database\Driver\DriverInterface;

use Arcano\Database\Injection\ParameterInterface;

use Arcano\Database\Query\QueryParameters;

use Arcano\Database\Driver\CursorOptions;
use Arcano\Database\Injection\FragmentInterface;
use Arcano\Database\Injection\Parameter;
use Arcano\Database\Query\SelectQuery;
use Arcano\Database\StatementInterface;
use Arcano\ORM\Service\Implementation\EntityFactory;
use Arcano\ORM\Heap\Node;
use Arcano\ORM\Select\Options\LoadOptions;
use Arcano\ORM\Service\EntityFactoryInterface;
use Arcano\ORM\Service\MapperProviderInterface;
use Arcano\ORM\Service\SourceProviderInterface;
use Arcano\ORM\Select\JoinableLoader;
use Arcano\ORM\RelationMap;
use Arcano\ORM\Select\Loader\HasManyLoader;
use Arcano\ORM\Select\QueryBuilder;
use Arcano\ORM\Select\RootLoader;
use Arcano\ORM\Select\ScopeInterface;
use Spiral\Pagination\PaginableInterface;

/**
 * Query builder and entity selector. Mocks SelectQuery. Attention, Selector does not mount RootLoader scope by default.
 *
 * Trait provides the ability to transparently configure underlying loader query.
 *
 * @template-covariant TEntity of object
 */
class Select implements \IteratorAggregate, \Countable, PaginableInterface
{
    // load relation data within same query
    public const SINGLE_QUERY = JoinableLoader::INLOAD;

    // load related data after the query
    public const OUTER_QUERY = JoinableLoader::POSTLOAD;

    protected int $limit = 0;
    protected int $offset = 0;

    /** Whether anything changed this select since it was built for its role. */
    private bool $customized = false;
    protected bool $allowGroupBy;
    private RootLoader $loader;
    private QueryBuilder $builder;
    private MapperProviderInterface $mapperProvider;
    private Heap\HeapInterface $heap;
    private SchemaInterface $schema;
    private EntityFactoryInterface $entityFactory;

    /**
     * While nothing but simple where/orderBy/limit/offset calls reached this select, they
     * are recorded instead of applied and the loader and builder are unset; {@see __get()}
     * applies them on the first access. The shape key identifies the query without its
     * values, so fetchAll() can run a cached statement.
     *
     * @var list<array{string, array, int|null}>
     */
    private array $recorded = [];
    private string $shape = '';
    /** @var list<scalar> */
    private array $shapeValues = [];
    /**
     * The unset loader and builder. A null builder means the loader is shared with
     * other selects and is cloned before first use.
     *
     * @var array{RootLoader, QueryBuilder|null}|null
     */
    private ?array $deferred = null;
    /** Shape plans shared by the clones of one select. */
    private \ArrayObject $shapes;

    private const RECORDABLE_OPERATORS = [
        '=' => true, '!=' => true, '<>' => true, '>' => true, '<' => true, '>=' => true, '<=' => true,
        'like' => true, 'not like' => true, 'LIKE' => true, 'NOT LIKE' => true,
    ];

    /**
     * @param class-string<TEntity>|string $role
     */
    public function __construct(
        ORMInterface $orm,
        string $role,
    ) {
        $this->heap = $orm->getHeap();
        $this->schema = $orm->getSchema();
        $this->mapperProvider = $orm->getService(MapperProviderInterface::class);
        $this->entityFactory = $orm->getService(EntityFactoryInterface::class);
        $this->allowGroupBy = $orm->getService(Options::class)->groupByToDeduplicate;
        $this->shapes = new \ArrayObject();
        $this->loader = new RootLoader(
            $orm->getSchema(),
            $orm->getService(SourceProviderInterface::class),
            $orm->getFactory(),
            $orm->resolveRole($role),
        );
        $this->builder = new QueryBuilder($this->loader->getQuery(), $this->loader);
    }

    /**
     * Override the source table for the query. Useful when the entity schema points to one table
     * but at runtime you need to read from a different one (e.g. an archive or partition).
     *
     * The entity mapping, column aliases and relations remain unchanged — only the
     * FROM clause is replaced:
     *
     *     // Read users from an archive table instead of the default one
     *     $select->from('user_archive')->where('id', 1)->fetchOne();
     *
     *     // Combine with relations — comments are still loaded from their own table
     *     $select->from('user_archive')
     *         ->load('comments')
     *         ->orderBy('id')
     *         ->fetchAll();
     *
     * @param non-empty-string $table
     *
     * @return static<TEntity>
     */
    public function from(string $table): static
    {
        $this->customized = true;
        $this->loader->getQuery()->from(\sprintf('%s AS %s', $table, $this->loader->getAlias()));
        return $this;
    }

    /**
     * Mark query to return only distinct results.
     *
     *     $select->distinct()->fetchAll();
     *
     * @return static<TEntity>
     */
    public function distinct(): static
    {
        $this->customized = true;
        $this->builder->distinct();
        return $this;
    }

    /**
     * Create new Selector with applied scope. By default no scope used.
     *
     * @return static<TEntity>
     */
    public function scope(?ScopeInterface $scope = null): self
    {
        $scope === null or $this->customized = true;
        $this->loader->setScope($scope);

        return $this;
    }

    /**
     * Get Query proxy.
     */
    public function getBuilder(): QueryBuilder
    {
        $this->customized = true;
        return $this->builder;
    }

    /**
     * Compiled SQL query, changes in this query would not affect Selector state (but bound parameters will).
     */
    public function buildQuery(): SelectQuery
    {
        return $this->addGroupByPK()->loader->buildQuery();
    }

    /**
     * Shortcut to where method to set AND condition for entity primary key.
     *
     * @psalm-param string|int|list<string|int>|object ...$ids
     *
     * @return static<TEntity>
     */
    public function wherePK(string|int|array|object ...$ids): self
    {
        $this->customized = true;
        $pk = $this->loader->getPrimaryFields();

        if (\count($pk) > 1) {
            return $this->buildCompositePKQuery($pk, $ids);
        }
        $pk = \current($pk);

        return \count($ids) > 1
            ? $this->__call('where', [$pk, new Parameter($ids)])
            : $this->__call('where', [$pk, \current($ids)]);
    }

    /**
     * Add a WHERE condition to the query. Supports multiple calling conventions.
     *
     * Simple equality and comparison:
     *
     *     // Equality (column, value)
     *     $select->where('id', 2);
     *     $select->where('status', 'active');
     *     // Operator comparison (column, operator, value)
     *     $select->where('level', '>=', 10);
     *     $select->where('deleted_at', '=', null);
     *     $select->where('name', 'LIKE', '%john%');
     *     // BETWEEN (column, 'between', from, to)
     *     $select->where('comments.id', 'between', 1, 4);
     *
     * Array syntax allows multiple conditions (AND by default):
     *
     *     // Simple equalities
     *     $select->where(['id' => 2]);
     *     $select->where(['key1' => 1, 'key2' => 2]);
     *     // IN clause using Parameter
     *     $select->where(['id' => new Parameter([1, 2])]);
     *     // Operator syntax
     *     $select->where(['id' => ['>' => 0, '<' => 3]]);
     *     $select->where(['comments.id' => ['between' => [1, 4]]]);
     *
     * Logical grouping with "@or" / "@AND":
     *
     *     $select->where([
     *         "@or" => [
     *             ['comments.message' => 'msg 1'],
     *             ['comments.message' => 'msg 3'],
     *         ],
     *     ]);
     *
     *     $select->where([
     *         "@AND" => [
     *             ['name' => 'Valeriy'],
     *             ['level' => ['>=' => 10]],
     *         ],
     *     ]);
     *
     * Closure for nested or complex conditions:
     *
     *     $select->where(function (\Arcano\ORM\Select\QueryBuilder $q): void {
     *         $q->where('id', 2);
     *     });
     *
     *     // Combining AND / OR inside a closure
     *     $select->where(function (\Arcano\ORM\Select\QueryBuilder $q): void {
     *         $q->where('comments.message', 'msg 3')
     *           ->orWhere(function (\Arcano\ORM\Select\QueryBuilder $q): void {
     *               $q->where('id', 1);
     *           });
     *     });
     *
     * Raw SQL fragments and expressions:
     *
     *     // Fragment with bound parameters
     *     $select->where(new \Arcano\Database\Injection\Fragment('fp.filter_id = ?', 5));
     *     // Expression as a value
     *     $select->where('comments.id', new \Arcano\Database\Injection\Expression('user.id'));
     *
     * When used with relations joined via {@see with()}, prefix columns with the relation alias:
     *
     *     $select->with('comments')->where('comments.approved', true);
     *     $select->with('posts.comments')->where('posts_comments.approved', true);
     *
     * @param mixed ...$args [(column, value), (column, operator, value), (array), (closure), (Fragment)]
     *
     * @return static<TEntity>
     */
    public function where(mixed ...$args): static
    {
        if (!$this->customized && $this->record('where', $args)) {
            return $this;
        }
        $this->customized = true;
        $this->builder->where(...$args);
        return $this;
    }

    /**
     * Add an AND WHERE condition to the query. Behaves identically to {@see where()},
     * but explicitly uses AND conjunction when chaining multiple conditions.
     *
     *     $select->where('status', 'active')->andWhere('balance', '>', 0);
     *     $select->andWhere(['role' => 'admin', 'active' => true]);
     *
     * @param mixed ...$args [(column, value), (column, operator, value), (array), (closure), (Fragment)]
     *
     * @return static<TEntity>
     *
     * @see where()
     */
    public function andWhere(mixed ...$args): static
    {
        $this->customized = true;
        $this->builder->andWhere(...$args);
        return $this;
    }

    /**
     * Add an OR WHERE condition to the query. Accepts the same arguments as {@see where()},
     * but uses OR conjunction.
     *
     *     $select->where('id', 1)->orWhere('id', 2);
     *
     *     // Closure for grouped OR conditions
     *     $select->where('status', 'active')->orWhere(function (\Arcano\ORM\Select\QueryBuilder $q): void {
     *         $q->where('role', 'admin')
     *           ->where('balance', '>', 0);
     *     });
     *
     * @param mixed ...$args [(column, value), (column, operator, value), (array), (closure), (Fragment)]
     *
     * @return static<TEntity>
     *
     * @see where()
     */
    public function orWhere(mixed ...$args): static
    {
        $this->customized = true;
        $this->builder->orWhere(...$args);
        return $this;
    }

    /**
     * Add a HAVING condition to the query. Typically used with {@see SelectQuery::groupBy()}
     * to filter aggregated results.
     *
     *     $select->having('COUNT(comments.id)', '>', 5);
     *     $select->having(['COUNT(id)' => ['>=' => 10]]);
     *
     * @param mixed ...$args [(column, value), (column, operator, value), (array), (closure)]
     *
     * @return static<TEntity>
     */
    public function having(mixed ...$args): static
    {
        $this->customized = true;
        $this->builder->having(...$args);
        return $this;
    }

    /**
     * Add an AND HAVING condition to the query.
     *
     *     $select->having('COUNT(id)', '>', 5)->andHaving('SUM(balance)', '<', 1000);
     *
     * @param mixed ...$args [(column, value), (column, operator, value), (array), (closure)]
     *
     * @return static<TEntity>
     *
     * @see having()
     */
    public function andHaving(mixed ...$args): static
    {
        $this->customized = true;
        $this->builder->andHaving(...$args);
        return $this;
    }

    /**
     * Add an OR HAVING condition to the query.
     *
     *     $select->having('COUNT(id)', '>', 10)->orHaving('SUM(balance)', '>', 1000);
     *
     * @param mixed ...$args [(column, value), (column, operator, value), (array), (closure)]
     *
     * @return static<TEntity>
     *
     * @see having()
     */
    public function orHaving(mixed ...$args): static
    {
        $this->customized = true;
        $this->builder->orHaving(...$args);
        return $this;
    }

    /**
     * Sort results by column, expression or multiple columns at once.
     *
     *     $select->orderBy('id');
     *     $select->orderBy('created_at', 'DESC');
     *
     *     // Multiple columns
     *     $select->orderBy([
     *         'id' => 'ASC',
     *         'name' => 'DESC',
     *     ]);
     *
     *     // Raw expression (direction is ignored)
     *     $select->orderBy(new \Arcano\Database\Injection\Fragment('RAND()'));
     *
     * @param non-empty-string|FragmentInterface|array<non-empty-string, non-empty-string> $expression
     * @param 'ASC'|'DESC'|null $direction Sorting direction, default ASC.
     *
     * @return static<TEntity>
     */
    public function orderBy(string|FragmentInterface|array $expression, ?string $direction = 'ASC'): static
    {
        if (!$this->customized && $this->record('orderBy', [$expression, $direction])) {
            return $this;
        }
        $this->customized = true;
        $this->builder->orderBy($expression, $direction);
        return $this;
    }

    /**
     * Add a FOR UPDATE lock to the query. Selected rows will be locked for the duration
     * of the current transaction, preventing other transactions from modifying them.
     *
     *     // Inside a transaction
     *     $user = $select->where('id', 1)->forUpdate()->fetchOne();
     *     $user->balance -= 100;
     *
     * @return static<TEntity>
     */
    public function forUpdate(): static
    {
        $this->customized = true;
        $this->builder->forUpdate();
        return $this;
    }

    /**
     * Filter by JSON field value using exact match.
     *
     *     $select->whereJson('settings->theme', 'dark');
     *     $select->whereJson('meta->score', 10);
     *
     * @return static<TEntity>
     */
    public function whereJson(string $path, mixed $value): static
    {
        $this->customized = true;
        $this->builder->whereJson($path, $value);
        return $this;
    }

    /**
     * OR version of {@see whereJson()}.
     *
     *     $select->whereJson('settings->theme', 'dark')
     *         ->orWhereJson('settings->theme', 'light');
     *
     * @return static<TEntity>
     */
    public function orWhereJson(string $path, mixed $value): static
    {
        $this->customized = true;
        $this->builder->orWhereJson($path, $value);
        return $this;
    }

    /**
     * Filter rows where a JSON array or object contains the given value.
     *
     *     $select->whereJsonContains('tags', 'php');
     *     $select->whereJsonContains('meta->roles', 'admin');
     *
     * @return static<TEntity>
     */
    public function whereJsonContains(string $path, mixed $value, bool $encode = true, bool $validate = true): static
    {
        $this->customized = true;
        $this->builder->whereJsonContains($path, $value, $encode, $validate);
        return $this;
    }

    /**
     * OR version of {@see whereJsonContains()}.
     *
     *     $select->whereJsonContains('tags', 'php')
     *         ->orWhereJsonContains('tags', 'go');
     *
     * @return static<TEntity>
     */
    public function orWhereJsonContains(string $path, mixed $value, bool $encode = true, bool $validate = true): static
    {
        $this->customized = true;
        $this->builder->orWhereJsonContains($path, $value, $encode, $validate);
        return $this;
    }

    /**
     * Filter rows where a JSON array or object does NOT contain the given value.
     *
     *     $select->whereJsonDoesntContain('tags', 'deprecated');
     *
     * @return static<TEntity>
     */
    public function whereJsonDoesntContain(string $path, mixed $value, bool $encode = true, bool $validate = true): static
    {
        $this->customized = true;
        $this->builder->whereJsonDoesntContain($path, $value, $encode, $validate);
        return $this;
    }

    /**
     * OR version of {@see whereJsonDoesntContain()}.
     *
     *     $select->whereJsonDoesntContain('tags', 'a')
     *         ->orWhereJsonDoesntContain('tags', 'b');
     *
     * @return static<TEntity>
     */
    public function orWhereJsonDoesntContain(string $path, mixed $value, bool $encode = true, bool $validate = true): static
    {
        $this->customized = true;
        $this->builder->orWhereJsonDoesntContain($path, $value, $encode, $validate);
        return $this;
    }

    /**
     * Filter rows where a key exists in a JSON object.
     *
     *     $select->whereJsonContainsKey('settings->notifications');
     *
     * @return static<TEntity>
     */
    public function whereJsonContainsKey(string $path): static
    {
        $this->customized = true;
        $this->builder->whereJsonContainsKey($path);
        return $this;
    }

    /**
     * OR version of {@see whereJsonContainsKey()}.
     *
     *     $select->whereJsonContainsKey('settings->email')
     *         ->orWhereJsonContainsKey('settings->sms');
     *
     * @return static<TEntity>
     */
    public function orWhereJsonContainsKey(string $path): static
    {
        $this->customized = true;
        $this->builder->orWhereJsonContainsKey($path);
        return $this;
    }

    /**
     * Filter rows where a key does NOT exist in a JSON object.
     *
     *     $select->whereJsonDoesntContainKey('settings->legacy_flag');
     *
     * @return static<TEntity>
     */
    public function whereJsonDoesntContainKey(string $path): static
    {
        $this->customized = true;
        $this->builder->whereJsonDoesntContainKey($path);
        return $this;
    }

    /**
     * OR version of {@see whereJsonDoesntContainKey()}.
     *
     *     $select->whereJsonDoesntContainKey('settings->a')
     *         ->orWhereJsonDoesntContainKey('settings->b');
     *
     * @return static<TEntity>
     */
    public function orWhereJsonDoesntContainKey(string $path): static
    {
        $this->customized = true;
        $this->builder->orWhereJsonDoesntContainKey($path);
        return $this;
    }

    /**
     * Filter rows by the length of a JSON array.
     *
     *     $select->whereJsonLength('tags', 3);
     *     $select->whereJsonLength('tags', 5, '>');
     *
     * @return static<TEntity>
     */
    public function whereJsonLength(string $path, int $length, string $operator = '='): static
    {
        $this->customized = true;
        $this->builder->whereJsonLength($path, $length, $operator);
        return $this;
    }

    /**
     * OR version of {@see whereJsonLength()}.
     *
     *     $select->whereJsonLength('tags', 0)
     *         ->orWhereJsonLength('roles', 0);
     *
     * @return static<TEntity>
     */
    public function orWhereJsonLength(string $path, int $length, string $operator = '='): static
    {
        $this->customized = true;
        $this->builder->orWhereJsonLength($path, $length, $operator);
        return $this;
    }

    /**
     * Attention, column will be quoted by driver!
     *
     * @param non-empty-string|null $column When column is null DISTINCT(PK) will be generated.
     */
    public function count(?string $column = null): int
    {
        if ($column === null) {
            $pk = (array) $this->loader->getPK();
            $column = \count($pk) > 1
                ? '*'
                : \sprintf('DISTINCT(%s)', \reset($pk));
        }

        return (int) $this->builder->withQuery(
            $this->buildQuery(),
        )->count($column);
    }

    /**
     * Perform AVG aggregation on the given column or expression.
     *
     *     $avgBalance = $select->avg('balance');
     *     $avgScore = $select->where('status', 'active')->avg('score');
     *
     * @param non-empty-string $identifier Column or expression to aggregate.
     */
    public function avg(string $identifier): mixed
    {
        return $this->builder->withQuery(
            $this->buildQuery(),
        )->avg($identifier);
    }

    /**
     * Perform MIN aggregation on the given column or expression.
     *
     *     $minBalance = $select->min('balance');
     *     $minPrice = $select->where('active', true)->min('price');
     *
     * @param non-empty-string $identifier Column or expression to aggregate.
     */
    public function min(string $identifier): mixed
    {
        return $this->builder->withQuery(
            $this->buildQuery(),
        )->min($identifier);
    }

    /**
     * Perform MAX aggregation on the given column or expression.
     *
     *     $maxBalance = $select->max('balance');
     *     $maxLevel = $select->where('role', 'admin')->max('level');
     *
     * @param non-empty-string $identifier Column or expression to aggregate.
     */
    public function max(string $identifier): mixed
    {
        return $this->builder->withQuery(
            $this->buildQuery(),
        )->max($identifier);
    }

    /**
     * Perform SUM aggregation on the given column or expression.
     *
     *     $totalBalance = $select->sum('balance');
     *     $totalSpent = $select->where('year', 2025)->sum('amount');
     *
     * @param non-empty-string $identifier Column or expression to aggregate.
     */
    public function sum(string $identifier): mixed
    {
        return $this->builder->withQuery(
            $this->buildQuery(),
        )->sum($identifier);
    }

    /**
     * @return static<TEntity>
     */
    public function limit(int $limit): self
    {
        $this->limit = $limit;
        if (!$this->customized && $this->record('limit', [$limit])) {
            return $this;
        }
        $this->customized = true;
        $this->loader->getQuery()->limit($limit);

        return $this;
    }

    /**
     * @return static<TEntity>
     */
    public function offset(int $offset): self
    {
        $this->offset = $offset;
        if (!$this->customized && $this->record('offset', [$offset])) {
            return $this;
        }
        $this->customized = true;
        $this->loader->getQuery()->offset($offset);

        return $this;
    }

    /**
     * Pre-load related data for the given relation(s).
     *
     * The loader automatically selects the most efficient strategy (additional query or JOIN).
     * Loaded data is populated into entity relations. Use "." to specify nested relations.
     *
     * Use typed DTO options for a safe, discoverable API:
     *
     *     use Arcano\ORM\Select\Options\HasManyLoadOptions;
     *     use Arcano\ORM\Select\Options\LoadMethod;
     *
     *     // Load comments using a separate query (default for HasMany)
     *     $select->load('comments', new HasManyLoadOptions());
     *
     *     // Force loading within the same query via JOIN
     *     $select->load('comments', new HasManyLoadOptions(
     *         method: LoadMethod::SingleQuery,
     *     ));
     *
     *     // Custom WHERE / ORDER BY
     *     $select->load('comments', new HasManyLoadOptions(
     *         where: ['@.status' => 'public'],
     *         orderBy: ['@.created_at' => 'DESC'],
     *     ));
     *
     *     // Load from an archive table
     *     $select->load('comments', new HasManyLoadOptions(
     *         table: 'comment_archive',
     *     ));
     *
     * Available DTO classes (one per relation type):
     * - {@see \Arcano\ORM\Select\Options\HasOneLoadOptions}
     * - {@see \Arcano\ORM\Select\Options\HasManyLoadOptions}
     * - {@see \Arcano\ORM\Select\Options\BelongsToLoadOptions} applicable for both Belongs To and Refers To relations
     * - {@see \Arcano\ORM\Select\Options\ManyToManyLoadOptions}
     * - {@see \Arcano\ORM\Select\Options\MorphedHasOneLoadOptions}
     * - {@see \Arcano\ORM\Select\Options\MorphedHasManyLoadOptions}
     * - {@see \Arcano\ORM\Select\Options\BelongsToMorphedLoadOptions}
     *
     * Raw array options are still supported for backwards compatibility:
     *
     *     $select->load('comments', ['where' => ['@.status' => 'public']]);
     *
     * Multiple relations can be loaded at once. The second argument applies
     * shared options to every relation in the list:
     *
     *     $select->load(['posts', 'comments', 'profile']);
     *     $select->load(['posts', 'comments'], new JoinableLoadOptions(
     *         method: LoadMethod::SingleQuery,
     *     ));
     *
     * @see with()
     *
     * @return static<TEntity>
     */
    public function load(array|string $relation, LoadOptions|array $options = []): self
    {
        $this->customized = true;
        if (\is_string($relation)) {
            $this->loader->loadRelation($relation, $options, false, true);

            return $this;
        }

        foreach ($relation as $name => $subOption) {
            if (\is_string($subOption)) {
                // array of relation names
                $this->load($subOption, $options);
            } else {
                // multiple relations or relation with addition load options
                $this->load($name, $subOption);
            }
        }

        return $this;
    }

    /**
     * With method is very similar to load() one, except it will always include related data to
     * parent query using INNER JOIN, this method can be applied only to ORM loaders and relations
     * using same database as parent record.
     *
     * Method generally used to filter data based on some relation condition. Attention, with()
     * method WILL NOT load relation data, it will only make it accessible in query.
     *
     * By default joined tables will be available in query based on relation name, you can change
     * joined table alias using relation option "alias".
     *
     * Do not forget to set DISTINCT flag while including HAS_MANY and MANY_TO_MANY relations. In
     * other scenario you will not able to paginate data well.
     *
     * Examples:
     *
     *     // Find all users who have comments comments
     *     User::find()->with('comments');
     *
     *     // Find all users who have approved comments (we can use comments table alias in where statement)
     *     User::find()->with('comments')->where('comments.approved', true);
     *
     *     // Find all users who have posts which have approved comments
     *     User::find()->with('posts.comments')->where('posts_comments.approved', true);
     *
     *     // Custom join alias for post comments relation
     *     $user->with('posts.comments', [
     *         'as' => 'comments'
     *     ])->where('comments.approved', true);
     *
     *     // If you joining MANY_TO_MANY relation you will be able to use pivot table used as relation name plus "_pivot" postfix. Let's load all users with approved tags.
     *     $user->with('tags')->where('tags_pivot.approved', true);
     *
     *     // You can also use custom alias for pivot table as well
     *     User::find()->with('tags', [
     *         'pivotAlias' => 'tags_connection'
     *     ])
     *     ->where('tags_connection.approved', false);
     *
     *
     * You can safely combine with() and load() methods.
     *
     *     // Load all users with approved comments and pre-load all their comments
     *     User::find()
     *         ->with('comments')
     *         ->where('comments.approved', true)->load('comments');
     *
     * You can also use custom conditions in this case, let's find all users with approved
     * comments and pre-load such approved comments
     *
     *     User::find()
     *             ->with('comments')
     *             ->where('comments.approved', true)
     *             ->load('comments', [
     *                 'where' => ['{@}.approved' => true]
     *             ]);
     *
     * As you might notice previous construction will create 2 queries, however we can simplify
     * this construction to use already joined table as source of data for relation via "using" keyword
     *
     *     User::find()
     *         ->with('comments')
     *         ->where('comments.approved', true)
     *         ->load('comments', ['using' => 'comments']);
     *
     * You will get only one query with INNER JOIN, to better understand this example let's use
     * custom alias for comments in with() method.
     *
     *     User::find()
     *         ->with('comments', ['as' => 'commentsR'])
     *         ->where('commentsR.approved', true)
     *         ->load('comments', ['using' => 'commentsR']);
     *
     * To use with() twice on the same relation, you can use `alias` option.
     *
     *     Country::find()
     *         // Find all translations
     *         ->with('translations', [ 'as' => 'trans'])
     *         ->load('translations', ['using' => 'trans'])
     *         // Second `with` for sorting only
     *         ->with('translations', [
     *             // Alias for SQL
     *             'as' => 'transEn',
     *             // Alias for ORM to not to overwrite previous `with`
     *              'alias' => 'translations-en',
     *              'method' => JoinableLoader::LEFT_JOIN,
     *              'where' => ['locale' => 'en'],
     *          ])
     *          ->orderBy('transEn.title', 'ASC');
     *
     * @return static<TEntity>
     *
     * @see load()
     */
    public function with(string|array $relation, array $options = []): self
    {
        $this->customized = true;
        if (\is_string($relation)) {
            $this->loader->loadRelation($relation, $options, true, false);

            return $this;
        }

        foreach ($relation as $name => $subOption) {
            if (\is_string($subOption)) {
                //Array of relation names
                $this->with($subOption, []);
            } else {
                //Multiple relations or relation with addition load options
                $this->with($name, $subOption);
            }
        }

        return $this;
    }

    /**
     * The compiled query of `findByPK()` for a single key, or null when this
     * select or its role needs the general path (scope, inheritance, eager or
     * composite keys, any customization).
     *
     * @internal
     *
     * @return array{DriverInterface, string, array, int, string, list<string>}|null
     */
    public function primaryKeyPlan(): ?array
    {
        $role = $this->loader->getTarget();
        if (
            $this->customized
            || $this->shape !== ''
            || $this->loader->hasChildren()
            || \count($this->loader->getPrimaryFields()) !== 1
            || $this->schema->define($role, SchemaInterface::SCOPE) !== null
            || $this->schema->define($role, SchemaInterface::PARENT) !== null
            || ($this->schema->define($role, SchemaInterface::CHILDREN) ?? []) !== []
        ) {
            return null;
        }

        $sentinel = "\0arcano-primary-key\0";
        $select = (clone $this)->wherePK($sentinel)->limit(1);
        if ($select->loader->hasChildren()) {
            return null;
        }
        $query = $select->loader->buildQuery();
        $parameters = new QueryParameters();
        $sql = $query->sqlStatement($parameters);
        $values = $parameters->getParameters();
        foreach ($values as $index => $value) {
            if (($value instanceof ParameterInterface ? $value->getValue() : $value) === $sentinel) {
                return [$query->getDriver(), $sql, $values, $index, $select->loader->getTarget(), $select->loader->getColumnNames(), $select->rowMaker()];
            }
        }

        return null;
    }

    /**
     * Runs a {@see primaryKeyPlan()} for one key.
     *
     * @internal
     */
    public function fetchByPrimaryKeyPlan(array $plan, int|string $id): ?object
    {
        [$driver, $sql, $values, $index, $role, $columns, $maker] = $plan;
        $values[$index] = $values[$index] instanceof ParameterInterface ? new Parameter($id) : $id;
        $statement = $driver->query($sql, $values);
        $row = $statement->fetch(StatementInterface::FETCH_NUM);
        $statement->close();
        if ($row === false) {
            return null;
        }

        if ($maker !== null) {
            return $maker([$row])[0];
        }

        // A root-only row: the parser would only name its columns.
        return $this->entityFactory->make($role, \array_combine($columns, $row), Node::MANAGED, typecast: true);
    }

    /**
     * Find one entity or return null. Method provides the ability to configure custom query parameters.
     *
     * @return TEntity|null
     */
    public function fetchOne(?array $query = null): ?object
    {
        $select = (clone $this)->where($query)->limit(1);
        $data = $select->loadData();

        if (!isset($data[0])) {
            return null;
        }

        /** @var TEntity $result */
        return $this->entityFactory->make($this->loader->getTarget(), $data[0], Node::MANAGED, typecast: true);
    }

    /**
     * Fetch all records in a form of array.
     *
     * @return list<TEntity>
     */
    public function fetchAll(): iterable
    {
        if (!$this->customized) {
            $plan = $this->shapes[$this->shape] ?? null;
            if ($plan === null && \count($this->shapes) < 1024) {
                $plan = $this->shapes[$this->shape] = $this->shapePlan();
            }
            if ($plan) {
                [$driver, $sql, $values, $slots, $maker] = $plan;
                foreach ($slots as $i => $index) {
                    $values[$index] = new Parameter($this->shapeValues[$i]);
                }
                $statement = $driver->query($sql, $values);
                $rows = $statement->fetchAll(StatementInterface::FETCH_NUM);
                $statement->close();

                return $maker($rows);
            }
        }

        $maker = $this->rowMaker();
        if ($maker === null) {
            $eager = $this->eagerPlan();
            return $eager === null
                ? \iterator_to_array($this->getIterator(), false)
                : $this->fetchEager(...$eager);
        }

        $statement = $this->addGroupByPK()->loader->buildQuery()->run();
        $rows = $statement->fetchAll(StatementInterface::FETCH_NUM);
        $statement->close();

        return $maker($rows);
    }

    /**
     * @internal Applies the recorded calls when the loader or the builder is first read.
     */
    public function __get(string $name): mixed
    {
        if ($this->deferred !== null && ($name === 'loader' || $name === 'builder')) {
            $this->applyRecorded();
            return $this->$name;
        }

        \trigger_error(\sprintf('Undefined property: %s::$%s', static::class, $name), \E_USER_WARNING);
        return null;
    }

    /**
     * Records a simple call instead of applying it. False when the call has another form.
     */
    private function record(string $method, array $args): bool
    {
        $value = null;
        switch ($method) {
            case 'where':
                $count = \count($args);
                if ($count === 2 && \array_is_list($args)) {
                    [$column, $value] = $args;
                    $operator = '=';
                } elseif ($count === 3 && \array_is_list($args)) {
                    [$column, $operator, $value] = $args;
                } else {
                    return false;
                }
                if (
                    !\is_string($column)
                    || !\is_string($operator)
                    || !isset(self::RECORDABLE_OPERATORS[$operator])
                    || !\is_scalar($value)
                ) {
                    return false;
                }
                $this->shape .= "w{$count}\0{$column}\0{$operator}\1";
                break;
            case 'orderBy':
                if (!\is_string($args[0]) || ($args[1] !== null && !\is_string($args[1]))) {
                    return false;
                }
                $this->shape .= "o\0{$args[0]}\0" . ($args[1] ?? "\2") . "\1";
                break;
            default: // limit, offset
                $this->shape .= "{$method}\0{$args[0]}\1";
        }

        if ($this->deferred === null) {
            $this->deferred = [$this->loader, $this->builder];
            unset($this->loader, $this->builder);
        }
        if ($value !== null) {
            $this->shapeValues[] = $value;
        }
        $this->recorded[] = [$method, $args, $value === null ? null : \count($this->shapeValues) - 1];

        return true;
    }

    /**
     * Lets clones share the loader until one of them uses it.
     *
     * @internal
     */
    public function share(): void
    {
        if ($this->deferred === null) {
            $this->deferred = [$this->loader, null];
            unset($this->loader, $this->builder);
        } elseif ($this->recorded === [] && $this->deferred[1] !== null) {
            $this->deferred[1] = null;
        }
    }

    private function applyRecorded(): void
    {
        $this->recorded === [] or $this->customized = true;
        if ($this->deferred === null) {
            return;
        }
        [$loader, $builder] = $this->deferred;
        if ($builder === null) {
            $loader = clone $loader;
            $builder = new QueryBuilder($loader->getQuery(), $loader);
        }
        [$this->loader, $this->builder] = [$loader, $builder];
        $this->deferred = null;
        foreach ($this->recorded as [$method, $args, $slot]) {
            if ($slot !== null) {
                $args[\array_key_last($args)] = $this->shapeValues[$slot];
            }
            match ($method) {
                'where' => $this->builder->where(...$args),
                'orderBy' => $this->builder->orderBy(...$args),
                'limit' => $this->loader->getQuery()->limit($args[0]),
                'offset' => $this->loader->getQuery()->offset($args[0]),
            };
        }
        $this->recorded = [];
    }

    /**
     * Compiles the recorded shape once with sentinel values and finds where each
     * value lands in the statement parameters. False when the shape can't be cached.
     */
    private function shapePlan(): array|false
    {
        $copy = clone $this;
        $sentinels = [];
        foreach ($copy->shapeValues as $i => $_) {
            $sentinels["\0arcano-value-{$i}\0"] = $i;
        }
        $copy->shapeValues = \array_keys($sentinels);
        $copy->applyRecorded();

        $maker = $copy->rowMaker();
        if ($maker === null) {
            return false;
        }
        $query = $copy->addGroupByPK()->loader->buildQuery();
        $parameters = new QueryParameters();
        $sql = $query->sqlStatement($parameters);
        $values = $parameters->getParameters();
        $slots = [];
        foreach ($values as $index => $value) {
            $raw = $value instanceof ParameterInterface ? $value->getValue() : $value;
            if (!\is_string($raw) || !\str_contains($raw, "\0arcano-value-")) {
                continue;
            }
            $slot = $sentinels[$raw] ?? null;
            if ($slot === null || isset($slots[$slot]) || !$value instanceof Parameter) {
                return false;
            }
            $slots[$slot] = $index;
        }

        return \count($slots) === \count($sentinels) ? [$query->getDriver(), $sql, $values, $slots, $maker] : false;
    }

    /**
     * Root entities plus has-many relations loaded with plain POSTLOAD queries, all
     * through row makers. Null when anything needs the parser.
     */
    private function eagerPlan(): ?array
    {
        if (!$this->entityFactory instanceof EntityFactory || $this->loader->getJoinedLoaders() !== []) {
            return null;
        }
        $role = $this->loader->getTarget();
        $relations = [];
        foreach ($this->loader->getLoadedLoaders() as $name => $loader) {
            if (!$loader instanceof HasManyLoader || !$loader->isPlainPostload()) {
                return null;
            }
            [$inner, $outer] = $loader->getRelationKeys();
            $columns = $loader->getColumnNames();
            if (\count($inner) !== 1 || \count($outer) !== 1 || !\in_array($outer[0], $columns, true)) {
                return null;
            }
            $childMaker = $this->entityFactory->rowMaker($loader->getTarget(), $columns, $outer[0]);
            if ($childMaker === null) {
                return null;
            }
            $relations[] = [$name, $loader, $childMaker, $inner[0], $outer[0], null];
        }
        if ($relations === []) {
            return null;
        }
        // When every relation is loaded here, new parents keep plain relation properties and
        // defer references only for a missing key.
        $relationMap = $this->entityFactory->relationMap($role);
        $all = \count($relations) === \count($relationMap->getRelations());
        $parentMaker = $this->entityFactory->rowMaker($role, $this->loader->getColumnNames(), deferRelations: !$all);
        if ($parentMaker === null) {
            return null;
        }
        foreach ($relations as &$relation) {
            $relation[5] = $this->entityFactory->freshRelationSetter($role, $relation[0], $all);
            if ($relation[5] === null) {
                return null;
            }
        }
        unset($relation);

        return [$role, $parentMaker, $relations, $all ? $relationMap : null];
    }

    /**
     * @param list<array{string, HasManyLoader, \Closure, string, string, \Closure}> $relations
     */
    private function fetchEager(string $role, \Closure $parentMaker, array $relations, ?RelationMap $undeferred): array
    {
        $query = $this->addGroupByPK()->loader->buildQuery();
        $whole = self::readsWholeTable($query);
        $statement = $query->run();
        $rows = $statement->fetchAll(StatementInterface::FETCH_NUM);
        $statement->close();
        $fresh = [];
        $parentNodes = [];
        $parents = $parentMaker($rows, $fresh, $parentNodes);
        try {
            $this->loadEager($role, $parents, $fresh, $parentNodes, $whole, $relations, $undeferred);
        } catch (\Throwable $e) {
            // New parents of a loaded maker must not stay in the heap without their relations.
            if ($undeferred !== null) {
                $this->entityFactory->discard(\array_filter(
                    $parents,
                    static fn (object $parent): bool => isset($fresh[\spl_object_id($parent)]),
                ));
            }
            throw $e;
        }

        return $parents;
    }

    private function loadEager(
        string $role,
        array $parents,
        array $fresh,
        array $parentNodes,
        bool $whole,
        array $relations,
        ?RelationMap $undeferred,
    ): void {
        foreach ($relations as [$name, $loader, $childMaker, $inner, $outer, $setter]) {
            // The parent keys, as the parser's reference index would give them.
            $keys = [];
            $parentKeys = [];
            foreach ($parentNodes as $i => $node) {
                $value = $node?->getData()[$inner] ?? null;
                $parentKeys[$i] = $value;
                $value === null or $keys[\is_int($value) ? $value : (string) $value] = $value;
            }
            $groups = [];
            if ($keys !== []) {
                $only = null;
                if ($whole) {
                    $rows = $this->childrenOf($loader);
                    $only = $keys;
                } else {
                    $references = [];
                    foreach ($keys as $value) {
                        $references[] = [$inner => $value];
                    }
                    $statement = $loader->buildPostloadQuery($references)->run();
                    $rows = $statement->fetchAll(StatementInterface::FETCH_NUM);
                    $statement->close();
                }
                $notTracked = $noNodes = null;
                $childMaker($rows, $notTracked, $noNodes, $groups, $only);
            }
            foreach ($parents as $i => $parent) {
                $value = $parentKeys[$i];
                if (!isset($fresh[\spl_object_id($parent)])) {
                    $value === null or $this->entityFactory->attachLoadedRelation(
                        $role,
                        $parent,
                        $name,
                        $groups[\is_int($value) ? $value : (string) $value] ?? [],
                        false,
                        $parentNodes[$i],
                    );
                } elseif ($value !== null) {
                    $setter($parent, $parentNodes[$i], $groups[\is_int($value) ? $value : (string) $value] ?? []);
                } elseif ($undeferred !== null) {
                    $this->entityFactory->deferLoaded($role, $parent, $parentNodes[$i], $name);
                }
            }
        }
    }

    /**
     * Whether the query reads every row of a single table.
     */
    private static function readsWholeTable(SelectQuery $query): bool
    {
        $tokens = $query->getTokens();

        return \count($tokens['from']) === 1
            && $tokens['join'] === []
            && $tokens['where'] === []
            && $tokens['having'] === []
            && !$tokens['limit']
            && !$tokens['offset']
            && $tokens['union'] === []
            && $tokens['intersect'] === []
            && $tokens['except'] === [];
    }

    /**
     * When the parents are the whole table, their children are among the related rows:
     * the relation query without the IN list, filtered by the row maker.
     */
    private function childrenOf(HasManyLoader $loader): array
    {
        $statement = $loader->buildPostloadQuery([])->run();
        try {
            return $statement->fetchAll(StatementInterface::FETCH_NUM);
        } finally {
            $statement->close();
        }
    }

    /**
     * Rows to entities without the parser when only the root table is loaded.
     */
    private function rowMaker(): ?\Closure
    {
        return !$this->loader->hasChildren() && $this->entityFactory instanceof EntityFactory
            ? $this->entityFactory->rowMaker($this->loader->getTarget(), $this->loader->getColumnNames())
            : null;
    }

    /**
     * @return Iterator<TEntity>
     */
    public function getIterator(bool $findInHeap = false): Iterator
    {
        return Iterator::createWithServices(
            $this->heap,
            $this->schema,
            $this->entityFactory,
            $this->loader->getTarget(),
            $this->loadData(),
            $findInHeap,
            typecast: true,
        );
    }

    /**
     * Stream entities from the database using a server-side cursor.
     *
     * The returned generator pulls rows lazily, hydrates them into entities, and
     * yields one entity at a time. Memory usage is bound by `$chunkSize` parents
     * plus the heap (which the caller is responsible for clearing between batches
     * via `$orm->getHeap()->clean()` if needed).
     *
     * Chunking happens at **parent boundaries**, not strict row counts. When a
     * relation is loaded inline and multiplies rows (HAS_MANY / MANY_TO_MANY), one
     * parent corresponds to several rows. A chunk is closed only when a new parent
     * PK appears in the stream, so a parent's full set of joined rows is never
     * split. A chunk may therefore contain more than `$chunkSize` rows when
     * children are present, but never more than `$chunkSize` distinct parents.
     *
     * For correctness with inline HAS_MANY / MANY_TO_MANY, rows of the same parent
     * must arrive contiguously. The cursor appends the root primary key to the
     * query's ORDER BY clause to guarantee this in the common case (no ORDER BY,
     * or ORDER BY on parent columns). If you order by a joined child column, the
     * scatter is not auto-fixed — order by parent columns instead.
     *
     * Requirements:
     * - The underlying driver must implement {@see \Arcano\Database\Driver\CursorInterface}
     *   (Postgres, SQLite, SQL Server). Other drivers throw a {@see \Arcano\Database\Exception\DriverException}.
     * - An active transaction is required on the underlying database before
     *   iteration starts.
     *
     * @param int<1, max> $chunkSize Maximum number of distinct parent entities
     *        per chunk. Controls when the ORM flushes the parser node and yields
     *        a batch of hydrated entities. This is **independent** of any
     *        DBAL-level chunk knob (e.g. Postgres `FETCH FORWARD N`), which is
     *        configured via `$options`.
     * @param CursorOptions|null $options Driver-specific cursor configuration forwarded
     *        to {@see \Arcano\Database\Database::cursor()}. Use driver-specific subclasses
     *        ({@see \Arcano\Database\Driver\Postgres\PostgresCursorOptions},
     *        {@see \Arcano\Database\Driver\SQLServer\SQLServerCursorOptions}) to tune
     *        Postgres FETCH FORWARD size, WITH HOLD, SQL Server cursor type, etc.
     *        Row fetch mode is forced to `FETCH_NUM` internally — the parser expects
     *        positional rows.
     *
     * Entity identity: if a yielded row corresponds to a PK already attached to the
     * heap, the same instance is returned. Fresh data from the current row is merged
     * into previously-unresolved relations ({@see \Arcano\ORM\Reference\ReferenceInterface})
     * via {@see EntityFactoryInterface::make()}.
     *
     * @return \Generator<int, TEntity>
     */
    public function cursor(int $chunkSize = 1000, CursorOptions $options = new CursorOptions()): \Generator
    {
        $query = $this->buildQuery();

        // Append root PK to ORDER BY so rows for the same parent stay contiguous
        // in the stream. PK is unique, so appending it never alters an existing
        // user-defined order — it only resolves ties.
        $pk = $this->loader->getPK();
        foreach ((array) $pk as $column) {
            $query->orderBy($column);
        }

        $database = $this->loader->getSource()->getDatabase();
        $role = $this->loader->getTarget();
        $loader = $this->loader;
        $extractPk = $this->buildPkExtractor();

        $rows = static function () use ($database, $query, $chunkSize, $loader, $extractPk, $options): \Generator {
            $node = $loader->createNode();
            $lastPk = null;
            $hasLast = false;
            $parentCount = 0;

            foreach ($database->cursor($query, $options, StatementInterface::FETCH_NUM) as $row) {
                $rowPk = $extractPk($row);

                if (!$hasLast || $rowPk !== $lastPk) {
                    if ($parentCount >= $chunkSize) {
                        // Current row starts a new parent; previous chunk is fully complete.
                        $loader->loadChildren($node, true);
                        yield from $node->getResult();
                        $node = $loader->createNode();
                        $parentCount = 0;
                    }
                    $parentCount++;
                    $lastPk = $rowPk;
                    $hasLast = true;
                }

                $node->parseRow(0, $row);
            }

            if ($parentCount > 0) {
                $loader->loadChildren($node, true);
                yield from $node->getResult();
            }
        };

        yield from Iterator::createWithServices(
            $this->heap,
            $this->schema,
            $this->entityFactory,
            $role,
            $rows(),
            // Hardcoded false — do NOT expose as a parameter. Iterator's findInHeap=true
            // takes a fast path that returns heap-attached entities untouched, skipping
            // the merge of fresh row data into previously-unresolved Reference relations.
            // For cursor streaming we always want the latest row to be merged in.
            findInHeap: false,
            typecast: true,
        );
    }

    /**
     * Load data tree from database and linked loaders in a form of array.
     *
     * @return array<array-key, array<non-empty-string, mixed>>
     */
    public function fetchData(bool $typecast = true): iterable
    {
        if (!$typecast) {
            return $this->loadData(false);
        }

        $mapper = $this->mapperProvider->getMapper($this->loader->getTarget());

        return \array_map([$mapper, 'cast'], $this->loadData(false));
    }

    /**
     * Compiled SQL statement.
     */
    public function sqlStatement(): string
    {
        return $this->buildQuery()->sqlStatement();
    }

    public function loadSubclasses(bool $load = true): self
    {
        $this->customized = true;
        $this->loader->setSubclassesLoading($load);
        return $this;
    }

    /**
     * Bypassing call to primary select query.
     */
    public function __call(string $name, array $arguments): mixed
    {
        $this->customized = true;
        $result = $this->builder->__call($name, $arguments);
        if ($result instanceof QueryBuilder) {
            return $this;
        }

        return $result;
    }

    /**
     * Cloning with loader tree cloning.
     *
     * @attention at this moment binded query parameters would't be cloned!
     */
    public function __clone()
    {
        if ($this->deferred !== null) {
            // Still unset: a shared loader stays shared, an owned one is copied.
            if ($this->deferred[1] !== null) {
                $loader = clone $this->deferred[0];
                $this->deferred = [$loader, new QueryBuilder($loader->getQuery(), $loader)];
            }
            return;
        }
        $this->loader = clone $this->loader;
        $this->builder = new QueryBuilder($this->loader->getQuery(), $this->loader);
    }

    /**
     * @param bool $addRole If true, the role name with the key `@role` will be added to the result set.
     * @return array<array-key, array<non-empty-string, mixed>>
     */
    protected function loadData(bool $addRole = true): array
    {
        $self = $this->addGroupByPK();
        $node = $self->loader->createNode();
        $self->loader->loadData($node, $addRole);
        return $node->getResult();
    }

    /**
     * Build a closure that pulls the root primary key value out of a FETCH_NUM row
     * produced by the cursor query. For composite PKs, the values are joined with a
     * NUL separator into a single comparable string.
     *
     * @return \Closure(array): (int|string|float|null)
     */
    private function buildPkExtractor(): \Closure
    {
        $columnNames = $this->loader->getColumnNames();
        $pkFields = $this->loader->getPrimaryFields();

        $positions = [];
        foreach ($pkFields as $field) {
            $idx = \array_search($field, $columnNames, true);
            if ($idx === false) {
                throw new \LogicException(\sprintf(
                    'Cursor cannot locate primary key column `%s` among root loader columns [%s].',
                    $field,
                    \implode(', ', $columnNames),
                ));
            }
            $positions[] = $idx;
        }

        if (\count($positions) === 1) {
            $p = $positions[0];
            return static fn(array $row): int|string|float|null => $row[$p];
        }

        return static function (array $row) use ($positions): string {
            $parts = [];
            foreach ($positions as $i) {
                $parts[] = (string) $row[$i];
            }
            return \implode("\0", $parts);
        };
    }

    /**
     * @param list<non-empty-string> $pk
     * @param list<array|int|object|string> $args
     *
     * @return static<TEntity>
     */
    private function buildCompositePKQuery(array $pk, array $args): self
    {
        $prepared = [];
        foreach ($args as $index => $values) {
            $values = $values instanceof Parameter ? $values->getValue() : $values;
            if (!\is_array($values)) {
                throw new \InvalidArgumentException('Composite primary key must be defined using an array.');
            }
            if (\count($pk) !== \count($values)) {
                throw new \InvalidArgumentException(
                    \sprintf('Primary key should contain %d values.', \count($pk)),
                );
            }

            $isAssoc = !\array_is_list($values);
            foreach ($values as $key => $value) {
                if ($isAssoc && !\in_array($key, $pk, true)) {
                    throw new \InvalidArgumentException(\sprintf('Primary key `%s` not found.', $key));
                }

                $key = $isAssoc ? $key : $pk[$key];
                $prepared[$index][$key] = $value;
            }
        }

        $this->__call('where', [static function (Select\QueryBuilder $q) use ($prepared): void {
            foreach ($prepared as $set) {
                $q->orWhere($set);
            }
        }]);

        return $this;
    }

    /**
     * Add group by for all primary keys if necessary.
     *
     * This is required to prevent duplicates in the result set when using LIMIT and OFFSET.
     *
     * @return static<TEntity> Original $this or cloned instance with group by added.
     */
    private function addGroupByPK(): self
    {
        if (!$this->allowGroupBy || $this->limit <= 1 && $this->offset === 0) {
            return $this;
        }

        // Check if there are no joins in the query
        if ($this->loader->getJoinedLoaders() === []) {
            // No joins, we can safely return the original instance
            return $this;
        }

        $self = clone $this;
        $self->loader->forceGroupBy();

        return $self;
    }
}

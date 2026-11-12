# Arcano

Data mapper ORM for PHP 8.4+, derived from [Cycle ORM](https://github.com/cycle/orm)
2.18.1 and [Cycle Database](https://github.com/cycle/database) 2.23.3. The API
stays Cycle-compatible under the `Arcano\ORM` and `Arcano\Database` namespaces;
the hot paths (hydration, heap, eager loading, unit of work) were rewritten.

```bash
composer require pushinbr/arcano
```

Drivers: SQLite, MySQL, PostgreSQL and SQL Server. Includes the schema builder,
attribute entities and migrations (forks of the matching Cycle packages).

## Overhead

ORM time minus raw PDO time, SQLite in memory, PHP 8.5 with JIT
(`benchmarks/compare.sh`). Ratio = other ORM overhead / Arcano overhead.

- vs Cycle ORM: insert 6.8x, select_all 9.5x, find_pk 7.2x, eager 5.7x,
  update 6.3x, small_queries 6.1x.
- vs Eloquent 12: insert 6.4x, select_all 2.4x, find_pk 15x, eager 4.5x,
  update 8.9x, small_queries 7.7x.

Arcano keeps an identity map and a change snapshot per entity; Eloquent keeps
neither, which bounds the select_all gap. Details in
[docs/performance-plan.md](docs/performance-plan.md).

## Usage

```php
use Arcano\Annotated\Annotation\{Column, Entity};
use Arcano\Annotated\Annotation\Relation\HasMany;

#[Entity(role: 'author')]
class Author
{
    #[Column(type: 'primary')]
    public ?int $id = null;

    #[Column(type: 'string(120)')]
    public string $name = '';

    /** @var list<Post> */
    #[HasMany(target: Post::class, collection: 'array')]
    public array $posts = [];
}
```

Compile the schema once (cache the array in production) and build the ORM:

```php
use Arcano\Annotated\{Entities, MergeColumns, MergeIndexes, TableInheritance, Embeddings};
use Arcano\Annotated\Locator\{TokenizerEmbeddingLocator, TokenizerEntityLocator};
use Arcano\Database\Config\DatabaseConfig;
use Arcano\Database\DatabaseManager;
use Arcano\ORM\{EntityManager, Factory, ORM, Schema};
use Arcano\Schema\{Compiler, Registry};
use Arcano\Schema\Generator as G;
use Spiral\Tokenizer\{Config\TokenizerConfig, Tokenizer};

$dbal = new DatabaseManager(new DatabaseConfig([...]));
$classes = (new Tokenizer(new TokenizerConfig(['directories' => ['src/Entities']])))->classLocator();

$schema = (new Compiler())->compile(new Registry($dbal), [
    new G\ResetTables(),
    new Embeddings(new TokenizerEmbeddingLocator($classes)),
    new Entities(new TokenizerEntityLocator($classes)),
    new TableInheritance(),
    new MergeColumns(),
    new G\GenerateRelations(),
    new G\GenerateModifiers(),
    new G\ValidateEntities(),
    new G\RenderTables(),
    new G\RenderRelations(),
    new G\RenderModifiers(),
    new G\ForeignKeys(),
    new MergeIndexes(),
    new G\GenerateTypecast(),
]);

$orm = new ORM(new Factory($dbal), new Schema($schema));

$author = $orm->getRepository(Author::class)->select()->load('posts')->wherePK(1)->fetchOne();
(new EntityManager($orm))->persist($author)->run();
```

Migrations: `Arcano\Migrations\Migrator` runs the files of a directory, and
`Arcano\Schema\Generator\Migrations\GenerateMigrations` (added to the
generators above) writes a migration with the difference between the entities
and the database. In PAM HTTP, `ArcanoServiceProvider`, `ArcanoSchemaCompiler`
and `ArcanoMigrationManager` wire all of this.

Schemas, relations, mappers, typecasting, attributes and migrations follow the
Cycle documentation ([ORM](https://cycle-orm.dev/docs),
[attributes](https://cycle-orm.dev/docs/annotated-prerequisites),
[migrations](https://cycle-orm.dev/docs/database-migrations)) with the
`Arcano\` namespaces.

## Tests

`vendor/bin/phpunit --testsuite <unit|sqlite|mysql|postgres|sqlserver>`. The
server suites expect MySQL on 13306, PostgreSQL on 15432 and SQL Server on
11433 (see `tests/bootstrap-*.php` and `.github/workflows/tests.yml`).

## License

MIT. See [LICENSE](LICENSE) and [NOTICE](NOTICE).

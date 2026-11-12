# Arcano

Data mapper ORM for PHP 8.4+, derived from [Cycle ORM](https://github.com/cycle/orm)
2.18.1 and [Cycle Database](https://github.com/cycle/database) 2.23.3. The API
stays Cycle-compatible under the `Arcano\ORM` and `Arcano\Database` namespaces;
the hot paths (hydration, heap, eager loading, unit of work) were rewritten.

```bash
composer require pushinbr/arcano
```

Drivers: SQLite, MySQL, PostgreSQL and SQL Server.

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
use Arcano\Database\Config\DatabaseConfig;
use Arcano\Database\DatabaseManager;
use Arcano\ORM\{EntityManager, Factory, ORM, Schema};

$dbal = new DatabaseManager(new DatabaseConfig([...]));
$orm = new ORM(new Factory($dbal), new Schema([...]));

$user = $orm->getRepository(User::class)->findByPK(1);
(new EntityManager($orm))->persist($user)->run();
```

Schemas, relations, mappers and typecasting follow the
[Cycle ORM documentation](https://cycle-orm.dev/docs).

## License

MIT. See [LICENSE](LICENSE) and [NOTICE](NOTICE).

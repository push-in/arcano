<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Arcano\Schema\Generator\Migrations\Tests\Functional;

use Arcano\Annotated\Entities;
use Arcano\Annotated\Locator\TokenizerEntityLocator;
use Arcano\Annotated\MergeColumns;
use Arcano\Annotated\MergeIndexes;
use Arcano\Database\Config\DatabaseConfig;
use Arcano\Database\Database;
use Arcano\Database\DatabaseManager;
use Arcano\Database\Driver\DriverInterface;
use Arcano\Database\Driver\Handler;
use Arcano\Database\Schema\AbstractTable;
use Arcano\Database\Schema\Comparator;
use Arcano\Migrations\Config\MigrationConfig;
use Arcano\Migrations\FileRepository;
use Arcano\Migrations\Migrator;
use Arcano\ORM\Config\RelationConfig;
use Arcano\ORM\Factory;
use Arcano\ORM\ORM;
use Arcano\ORM\SchemaInterface;
use Arcano\Schema\Compiler;
use Arcano\Schema\Generator\GenerateRelations;
use Arcano\Schema\Generator\GenerateTypecast;
use Arcano\Schema\Generator\Migrations\GenerateMigrations;
use Arcano\Schema\Generator\Migrations\Tests\TestLogger;
use Arcano\Schema\Generator\RenderRelations;
use Arcano\Schema\Generator\RenderTables;
use Arcano\Schema\Generator\ResetTables;
use Arcano\Schema\Generator\ValidateEntities;
use Arcano\Schema\Registry;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Spiral\Attributes\AttributeReader;
use Spiral\Core\Container;
use Spiral\Files\Files;
use Spiral\Tokenizer\ClassesInterface;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\Tokenizer;

abstract class BaseTest extends TestCase
{
    // currently active driver
    public const DRIVER = null;
    public const CONFIG = [
        'directory' => __DIR__ . '/../../files/',
        'table' => 'migrations',
        'safe' => true,
        'namespace' => 'Migration',
    ];

    // tests configuration
    public static array $config;

    // cross test driver cache
    public static array $driverCache = [];
    protected DriverInterface $driver;
    protected ?DatabaseManager $dbal = null;
    protected ?ORM $orm = null;
    protected LoggerInterface $logger;
    protected ClassesInterface $locator;
    protected Migrator $migrator;

    /**
     * Calculates missing parameters for typecasting.
     */
    public function withSchema(SchemaInterface $schema): ORM
    {
        $this->orm = $this->orm->withSchema($schema);
        return $this->orm;
    }

    public function getDriver(): DriverInterface
    {
        if (isset(static::$driverCache[static::DRIVER])) {
            return static::$driverCache[static::DRIVER];
        }

        $config = self::$config[static::DRIVER];
        if (!isset($this->driver)) {
            $this->driver = $config->driver::create($config);
        }

        return static::$driverCache[static::DRIVER] = $this->driver;
    }

    /**
     * Init all we need.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->dbal = new DatabaseManager(new DatabaseConfig([
            'default' => 'default',
            'databases' => [],
        ]));
        $this->dbal->addDatabase(new Database(
            'default',
            '',
            $this->getDriver(),
        ));

        $this->dbal->addDatabase(new Database(
            'secondary',
            'secondary_',
            $this->getDriver(),
        ));

        $this->logger = new TestLogger();
        $this->getDriver()->setLogger($this->logger);

        if (self::$config['debug']) {
            $this->logger->display();
        }

        $this->logger = new TestLogger();
        $this->getDriver()->setLogger($this->logger);

        if (self::$config['debug']) {
            $this->logger->display();
        }

        $this->orm = new ORM(new Factory(
            $this->dbal,
            RelationConfig::getDefault(),
        ), new \Arcano\ORM\Schema([]));

        $tokenizer = new Tokenizer(new TokenizerConfig([
            'directories' => [__DIR__ . '/Fixtures'],
            'exclude' => [],
        ]));

        $this->locator = $tokenizer->classLocator();

        $config = new MigrationConfig(static::CONFIG);

        $this->migrator = new Migrator(
            $config,
            $this->dbal,
            new FileRepository(
                $config,
                new Container(),
            ),
        );

        $this->migrator->configure();
    }

    /**
     * Cleanup.
     */
    public function tearDown(): void
    {
        $files = new Files();
        foreach ($files->getFiles(\dirname(__DIR__, 2) . '/files', '*.php') as $file) {
            $files->delete($file);
            clearstatcache(true, $file);
        }

        $this->disableProfiling();
        $this->dropDatabase($this->dbal->database('default'));
        $this->orm = null;
        $this->dbal = null;
    }

    protected function migrate(string $directory): array
    {
        $tokenizer = new Tokenizer(new TokenizerConfig([
            'directories' => [$directory],
            'exclude' => [],
        ]));

        $locator = $tokenizer->classLocator();

        $reader = new AttributeReader();
        $r = new Registry($this->dbal);

        (new Compiler())->compile($r, [
            new Entities(new TokenizerEntityLocator($locator, $reader), $reader),
            new ResetTables(),
            new MergeColumns($reader),
            new GenerateRelations(),
            new ValidateEntities(),
            new RenderTables(),
            new RenderRelations(),
            new MergeIndexes($reader),
            new GenerateTypecast(),
            $this->getGenerateMigrations(),
        ]);

        $tables = [];
        foreach ($r as $e) {
            $tables[] = $r->getTableSchema($e);
        }
        return $tables;
    }

    protected function getDatabase(): Database
    {
        return $this->dbal->database('default');
    }

    protected function dropDatabase(?Database $database = null): void
    {
        if (empty($database)) {
            return;
        }

        foreach ($database->getTables() as $table) {
            $schema = $table->getSchema();

            foreach ($schema->getForeignKeys() as $foreign) {
                $schema->dropForeignKey($foreign->getColumns());
            }

            $schema->save(Handler::DROP_FOREIGN_KEYS);
        }

        foreach ($database->getTables() as $table) {
            $schema = $table->getSchema();
            $schema->declareDropped();
            $schema->save();
        }
    }

    /**
     * For debug purposes only.
     */
    protected function enableProfiling(): void
    {
        if ($this->logger !== null) {
            $this->logger->display();
        }
    }

    /**
     * For debug purposes only.
     */
    protected function disableProfiling(): void
    {
        if ($this->logger !== null) {
            $this->logger->hide();
        }
    }

    protected function assertSameAsInDB(AbstractTable $current): void
    {
        $source = $current->getState();
        $target = $current->getDriver()->getSchemaHandler()->getSchema($current->getName())->getState();

        // testing changes

        $this->assertSame(
            $source->getPrimaryKeys(),
            $target->getPrimaryKeys(),
            'Primary keys changed',
        );

        $this->assertSame(
            count($source->getColumns()),
            count($target->getColumns()),
            'Column number has changed',
        );

        $this->assertSame(
            count($source->getIndexes()),
            count($target->getIndexes()),
            'Index number has changed',
        );

        $this->assertSame(
            count($source->getForeignKeys()),
            count($target->getForeignKeys()),
            'FK number has changed',
        );

        // columns

        foreach ($source->getColumns() as $column) {
            $this->assertTrue(
                $target->hasColumn($column->getName()),
                "Column {$column} has been removed",
            );

            $this->assertTrue(
                $column->compare($target->findColumn($column->getName())),
                "Column {$column} has been changed",
            );
        }

        foreach ($target->getColumns() as $column) {
            $this->assertTrue(
                $source->hasColumn($column->getName()),
                "Column {$column} has been added",
            );

            $this->assertTrue(
                $column->compare($source->findColumn($column->getName())),
                "Column {$column} has been changed",
            );
        }

        // indexes

        foreach ($source->getIndexes() as $index) {
            $this->assertTrue(
                $target->hasIndex($index->getColumns()),
                "Index {$index->getName()} has been removed",
            );

            $this->assertTrue(
                $index->compare($target->findIndex($index->getColumns())),
                "Index {$index->getName()} has been changed",
            );
        }

        foreach ($target->getIndexes() as $index) {
            $this->assertTrue(
                $source->hasIndex($index->getColumns()),
                "Index {$index->getName()} has been removed",
            );

            $this->assertTrue(
                $index->compare($source->findIndex($index->getColumns())),
                "Index {$index->getName()} has been changed",
            );
        }

        // FK
        foreach ($source->getForeignKeys() as $key) {
            $this->assertTrue(
                $target->hasForeignKey($key->getColumns()),
                "FK {$key->getName()} has been removed",
            );

            $this->assertTrue(
                $key->compare($target->findForeignKey($key->getColumns())),
                "FK {$key->getName()} has been changed",
            );
        }

        foreach ($target->getForeignKeys() as $key) {
            $this->assertTrue(
                $source->hasForeignKey($key->getColumns()),
                "FK {$key->getName()} has been removed",
            );

            $this->assertTrue(
                $key->compare($source->findForeignKey($key->getColumns())),
                "FK {$key->getName()} has been changed",
            );
        }

        // everything else
        $comparator = new Comparator(
            $current->getState(),
            $current->getDriver()->getSchemaHandler()->getSchema($current->getName())->getState(),
        );

        if ($comparator->hasChanges()) {
            $this->fail($this->makeMessage($current->getName(), $comparator));
        }
    }

    protected function makeMessage(string $table, Comparator $comparator): string
    {
        if ($comparator->isPrimaryChanged()) {
            return "Table '{$table}' not synced, primary indexes are different.";
        }

        if ($comparator->droppedColumns()) {
            return "Table '{$table}' not synced, columns are missing.";
        }

        if ($comparator->addedColumns()) {
            return "Table '{$table}' not synced, new columns found.";
        }

        if ($comparator->alteredColumns()) {
            $names = [];
            foreach ($comparator->alteredColumns() as $pair) {
                $names[] = $pair[0]->getName();
                print_r($pair);
            }

            return "Table '{$table}' not synced, column(s) '" . implode(
                "', '",
                $names,
            ) . "' have been changed.";
        }

        if ($comparator->droppedForeignKeys()) {
            return "Table '{$table}' not synced, FKs are missing.";
        }

        if ($comparator->addedForeignKeys()) {
            return "Table '{$table}' not synced, new FKs found.";
        }


        return "Table '{$table}' not synced, no idea why, add more messages :P";
    }

    protected function getGenerateMigrations(): GenerateMigrations
    {
        return new GenerateMigrations($this->migrator->getRepository(), new MigrationConfig(static::CONFIG));
    }
}

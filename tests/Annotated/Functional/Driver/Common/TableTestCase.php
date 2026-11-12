<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Common;

use Arcano\Annotated\Entities;
use Arcano\Annotated\Exception\AnnotationException;
use Arcano\Annotated\Locator\TokenizerEntityLocator;
use Arcano\Annotated\MergeColumns;
use Arcano\Annotated\MergeIndexes;
use Arcano\ORM\Schema;
use Arcano\Schema\Compiler;
use Arcano\Schema\Generator\ForeignKeys;
use Arcano\Schema\Generator\RenderTables;
use Arcano\Schema\Generator\SyncTables;
use Arcano\Schema\Registry;
use Spiral\Attributes\AnnotationReader;
use Spiral\Attributes\AttributeReader;
use Spiral\Attributes\Composite\SelectiveReader;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\Tokenizer;

abstract class TableTestCase extends BaseTestCase
{
    public static function foreignKeyDirectoriesDataProvider(): \Traversable
    {
        yield [new AttributeReader(), '/Fixtures/Fixtures24/Class/DatabaseField'];
        yield [new AttributeReader(), '/Fixtures/Fixtures24/Class/PrimaryKey', 'id'];
        yield [new AttributeReader(), '/Fixtures/Fixtures24/Class/PropertyName'];

        yield [new AnnotationReader(), '/Fixtures/Fixtures24/Class/DatabaseField'];
        yield [new AnnotationReader(), '/Fixtures/Fixtures24/Class/PrimaryKey', 'id'];
        yield [new AnnotationReader(), '/Fixtures/Fixtures24/Class/PropertyName'];

        yield [
            new SelectiveReader([new AnnotationReader(), new AttributeReader()]),
            '/Fixtures/Fixtures24/Class/DatabaseField',
        ];
        yield [
            new SelectiveReader([new AnnotationReader(), new AttributeReader()]),
            '/Fixtures/Fixtures24/Class/PrimaryKey',
            'id',
        ];
        yield [
            new SelectiveReader([new AnnotationReader(), new AttributeReader()]),
            '/Fixtures/Fixtures24/Class/PropertyName',
        ];

        yield [new AnnotationReader(), '/Fixtures/Fixtures24/Entity/DatabaseField'];
        yield [new AnnotationReader(), '/Fixtures/Fixtures24/Entity/PrimaryKey', 'id'];
        yield [new AnnotationReader(), '/Fixtures/Fixtures24/Entity/PropertyName'];

        yield [new AttributeReader(), '/Fixtures/Fixtures24/Property/DatabaseField'];
        yield [new AttributeReader(), '/Fixtures/Fixtures24/Property/PrimaryKey', 'id'];
        yield [new AttributeReader(), '/Fixtures/Fixtures24/Property/PropertyName'];

        yield [new AnnotationReader(), '/Fixtures/Fixtures24/Property/DatabaseField'];
        yield [new AnnotationReader(), '/Fixtures/Fixtures24/Property/PrimaryKey', 'id'];
        yield [new AnnotationReader(), '/Fixtures/Fixtures24/Property/PropertyName'];

        yield [
            new SelectiveReader([new AnnotationReader(), new AttributeReader()]),
            '/Fixtures/Fixtures24/Property/DatabaseField',
        ];
        yield [
            new SelectiveReader([new AnnotationReader(), new AttributeReader()]),
            '/Fixtures/Fixtures24/Property/PrimaryKey',
            'id',
        ];
        yield [
            new SelectiveReader([new AnnotationReader(), new AttributeReader()]),
            '/Fixtures/Fixtures24/Property/PropertyName',
        ];
    }

    /**
     * @dataProvider allReadersProvider
     */
    public function testColumnsRendered(ReaderInterface $reader): void
    {
        $r = new Registry($this->dbal);
        (new Entities(new TokenizerEntityLocator($this->locator, $reader), $reader))->run($r);
        (new MergeColumns($reader))->run($r);
        (new RenderTables())->run($r);

        $this->assertTrue($r->hasTable($r->getEntity('withTable')));

        $schema = $r->getTableSchema($r->getEntity('withTable'));

        $this->assertTrue($schema->hasColumn('name'));
        $this->assertSame('string', $schema->column('status')->getType());

        $this->assertTrue($schema->hasColumn('status'));

        $this->assertSame('enum', $schema->column('status')->getAbstractType());
        $this->assertSame('active', $schema->column('status')->getDefaultValue());
        $this->assertSame(['active', 'disabled'], $schema->column('status')->getEnumValues());
    }

    /**
     * @dataProvider allReadersProvider
     */
    public function testColumnWithDifferentColumnNameAndProperty(ReaderInterface $reader): void
    {
        $r = new Registry($this->dbal);
        $schema = (new Compiler())->compile(
            $r,
            [
                new Entities(new TokenizerEntityLocator($this->locator, $reader), $reader),
                new MergeColumns($reader),
                new RenderTables(),
            ],
        );
        $this->assertArrayHasKey('withTable', $schema);
        $this->assertArrayHasKey('status_property', $schema['withTable'][Schema::COLUMNS]);
        $this->assertSame('status', $schema['withTable'][Schema::COLUMNS]['status_property']);
    }

    /**
     * @dataProvider allReadersProvider
     */
    public function testColumnWithoutColumnName(ReaderInterface $reader): void
    {
        $r = new Registry($this->dbal);
        $schema = (new Compiler())->compile(
            $r,
            [
                new Entities(new TokenizerEntityLocator($this->locator, $reader), $reader),
                new MergeColumns($reader),
                new RenderTables(),
            ],
        );
        $this->assertArrayHasKey('withTable', $schema);
        $this->assertArrayHasKey('no_column_name', $schema['withTable'][Schema::COLUMNS]);
        $this->assertSame('no_column_name', $schema['withTable'][Schema::COLUMNS]['no_column_name']);
    }

    public function testColumnInTableAnnotationByNumericKey(): void
    {
        $reader = new AnnotationReader();
        $tokenizer = new Tokenizer(new TokenizerConfig([
            'directories' => [__DIR__ . '/../../../Fixtures/Fixtures9'],
            'exclude' => [],
        ]));
        $locator = $tokenizer->classLocator();
        $r = new Registry($this->dbal);
        $schema = (new Compiler())->compile($r, [
            new Entities(new TokenizerEntityLocator($locator, $reader), $reader),
            new MergeColumns($reader),
            new RenderTables(),
        ]);

        $this->assertArrayHasKey('orderedIdx', $schema);
        $this->assertArrayHasKey('other_name', $schema['orderedIdx'][Schema::COLUMNS]);
        $this->assertSame('other_name', $schema['orderedIdx'][Schema::COLUMNS]['other_name']);
    }

    public function testColumnWithPropertyInTableAnnotationByNamedKey(): void
    {
        $reader = new AnnotationReader();
        $tokenizer = new Tokenizer(new TokenizerConfig([
            'directories' => [__DIR__ . '/../../../Fixtures/Fixtures11'],
            'exclude' => [],
        ]));
        $locator = $tokenizer->classLocator();
        $r = new Registry($this->dbal);

        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage(
            'Can not use name "name1" for Column of the `badEntity` role, because the '
            . '"property" field of the metadata class has already been set to "name2".',
        );

        (new Compiler())->compile($r, [
            new Entities(new TokenizerEntityLocator($locator, $reader), $reader),
            new MergeColumns($reader),
            new RenderTables(),
        ]);
    }

    /**
     * @dataProvider singularReadersProvider
     */
    public function testCompositePrimaryKey(ReaderInterface $reader): void
    {
        $r = new Registry($this->dbal);

        $tokenizer = new Tokenizer(new TokenizerConfig([
            'directories' => [__DIR__ . '/../../../Fixtures/Fixtures12'],
            'exclude' => [],
        ]));
        $locator = $tokenizer->classLocator();

        (new Entities(new TokenizerEntityLocator($locator, $reader), $reader))->run($r);
        (new MergeColumns($reader))->run($r);
        (new RenderTables())->run($r);
        (new MergeIndexes($reader))->run($r);
        (new SyncTables())->run($r);

        $schema = $r->getTableSchema($r->getEntity('compositePost'));

        $this->assertEquals(['id', 'user_id'], $schema->getPrimaryKeys());
    }

    /**
     * @dataProvider singularReadersProvider
     */
    public function testIndexWithEmptyColumnsShouldThrowAnException(ReaderInterface $reader): void
    {
        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage('Invalid index definition for `compositePost`. Column list can\'t be empty.');

        $r = new Registry($this->dbal);

        $tokenizer = new Tokenizer(new TokenizerConfig([
            'directories' => [__DIR__ . '/../../../Fixtures/Fixtures13'],
            'exclude' => [],
        ]));
        $locator = $tokenizer->classLocator();

        (new Entities(new TokenizerEntityLocator($locator, $reader), $reader))->run($r);
        (new MergeColumns($reader))->run($r);
        (new RenderTables())->run($r);
        (new MergeIndexes($reader))->run($r);
    }

    /**
     * @dataProvider allReadersProvider
     */
    public function testIndexes(ReaderInterface $reader): void
    {
        $r = new Registry($this->dbal);

        (new Entities(new TokenizerEntityLocator($this->locator, $reader), $reader))->run($r);
        (new MergeColumns($reader))->run($r);
        (new RenderTables())->run($r);
        (new MergeIndexes($reader))->run($r);
        (new SyncTables())->run($r);

        $this->assertTrue($r->hasTable($r->getEntity('withTable')));

        $schema = $r->getTableSchema($r->getEntity('withTable'));

        $this->assertTrue($schema->hasIndex(['name']));
        $this->assertTrue($schema->index(['name'])->isUnique());
        $this->assertSame('name_index', $schema->index(['name'])->getName());

        $this->assertTrue($schema->hasIndex(['status']));
    }

    /**
     * @dataProvider singularReadersProvider
     */
    public function testOrderedIndexes(ReaderInterface $reader): void
    {
        $tokenizer = new Tokenizer(new TokenizerConfig([
            'directories' => [__DIR__ . '/../../../Fixtures/Fixtures9'],
            'exclude' => [],
        ]));

        $locator = $tokenizer->classLocator();

        $r = new Registry($this->dbal);

        (new Entities(new TokenizerEntityLocator($locator, $reader), $reader))->run($r);
        (new MergeColumns($reader))->run($r);
        (new RenderTables())->run($r);
        (new MergeIndexes($reader))->run($r);
        (new SyncTables())->run($r);

        $this->assertTrue($r->hasTable($r->getEntity('orderedIdx')));

        $schema = $r->getTableSchema($r->getEntity('orderedIdx'));

        $this->assertTrue($schema->hasColumn('name'));
        $this->assertTrue($schema->hasIndex(['name', 'id DESC']));
    }

    /**
     * @dataProvider allReadersProvider
     */
    public function testNamingDefault(ReaderInterface $reader): void
    {
        $r = new Registry($this->dbal);
        (new Entities(new TokenizerEntityLocator($this->locator, $reader), $reader))->run($r);
        (new MergeColumns($reader))->run($r);
        (new RenderTables())->run($r);

        $this->assertTrue($r->hasTable($r->getEntity('withTable')));

        $schema = $r->getTableSchema($r->getEntity('withTable'));

        $this->assertSame('with_tables', $schema->getName());
    }

    /**
     * @dataProvider allReadersProvider
     */
    public function testNamingPluralize(ReaderInterface $reader): void
    {
        $r = new Registry($this->dbal);
        (new Entities(
            new TokenizerEntityLocator($this->locator, $reader),
            $reader,
            Entities::TABLE_NAMING_PLURAL,
        ))->run($r);
        (new MergeColumns($reader))->run($r);
        (new RenderTables())->run($r);

        $this->assertTrue($r->hasTable($r->getEntity('withTable')));

        $schema = $r->getTableSchema($r->getEntity('withTable'));

        $this->assertSame('with_tables', $schema->getName());
    }

    /**
     * @dataProvider allReadersProvider
     */
    public function testNamingSingular(ReaderInterface $reader): void
    {
        $r = new Registry($this->dbal);
        (new Entities(
            new TokenizerEntityLocator($this->locator, $reader),
            $reader,
            Entities::TABLE_NAMING_SINGULAR,
        ))->run($r);
        (new MergeColumns($reader))->run($r);
        (new RenderTables())->run($r);

        $this->assertTrue($r->hasTable($r->getEntity('withTable')));

        $schema = $r->getTableSchema($r->getEntity('withTable'));

        $this->assertSame('with_table', $schema->getName());
    }

    /**
     * @dataProvider allReadersProvider
     */
    public function testNamingNone(ReaderInterface $reader): void
    {
        $r = new Registry($this->dbal);
        (new Entities(
            new TokenizerEntityLocator($this->locator, $reader),
            $reader,
            Entities::TABLE_NAMING_NONE,
        ))->run($r);
        (new MergeColumns($reader))->run($r);
        (new RenderTables())->run($r);

        $this->assertTrue($r->hasTable($r->getEntity('withTable')));

        $schema = $r->getTableSchema($r->getEntity('withTable'));

        $this->assertSame('with_table', $schema->getName());
    }

    /**
     * @dataProvider allReadersProvider
     */
    public function testReadonlySchema(ReaderInterface $reader): void
    {
        $r = new Registry($this->dbal);
        (new Entities(new TokenizerEntityLocator($this->locator, $reader), $reader))->run($r);
        (new MergeColumns($reader))->run($r);
        (new RenderTables())->run($r);

        $this->assertTrue($r->hasTable($r->getEntity('simple')));

        $schema = $r->getTableSchema($r->getEntity('simple'));

        $this->assertTrue($schema->column('read_only_column')->isReadonlySchema());
    }

    /**
     * @dataProvider foreignKeyDirectoriesDataProvider
     */
    public function testForeignKeysAnnotationReader(
        ReaderInterface $reader,
        string $directory,
        string $outerKey = 'outer_key',
    ): void {
        $tokenizer = new Tokenizer(
            new TokenizerConfig([
                'directories' => [dirname(__DIR__, 3) . $directory],
                'exclude' => [],
            ]),
        );

        $locator = $tokenizer->classLocator();
        $registry = new Registry($this->dbal);

        (new Compiler())->compile($registry, [
            new Entities(new TokenizerEntityLocator($locator, $reader), $reader),
            new MergeColumns($reader),
            $t = new RenderTables(),
            new MergeIndexes($reader),
            new ForeignKeys(),
        ]);

        $t->getReflector()->run();

        $foreignKeys = $registry->getTableSchema($registry->getEntity('from'))->getForeignKeys();
        $expectedFk = array_shift($foreignKeys);

        $this->assertStringContainsString('from', $expectedFk->getTable());
        $this->assertStringContainsString('to', $expectedFk->getForeignTable());
        $this->assertSame(['inner_key'], $expectedFk->getColumns());
        $this->assertSame([$outerKey], $expectedFk->getForeignKeys());
        $this->assertSame('CASCADE', $expectedFk->getDeleteRule());
        $this->assertSame('CASCADE', $expectedFk->getUpdateRule());
        $this->assertTrue($expectedFk->hasIndex());
    }
}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Common\Relation;

use Arcano\Annotated\Embeddings;
use Arcano\Annotated\Entities;
use Arcano\Annotated\Locator\TokenizerEmbeddingLocator;
use Arcano\Annotated\Locator\TokenizerEntityLocator;
use Arcano\Annotated\MergeColumns;
use Arcano\Annotated\MergeIndexes;
use Arcano\Annotated\Tests\Fixtures\Fixtures26\CityTypecast;
use Arcano\Annotated\Tests\Functional\Driver\Common\BaseTestCase;
use Arcano\ORM\Relation;
use Arcano\ORM\Schema;
use Arcano\Schema\Compiler;
use Arcano\Schema\Generator\GenerateRelations;
use Arcano\Schema\Generator\GenerateTypecast;
use Arcano\Schema\Generator\RenderRelations;
use Arcano\Schema\Generator\RenderTables;
use Arcano\Schema\Generator\ResetTables;
use Arcano\Schema\Generator\SyncTables;
use Arcano\Schema\Registry;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\Tokenizer;

abstract class EmbeddedTestCase extends BaseTestCase
{
    /**
     * @dataProvider allReadersProvider
     */
    public function testRelation(ReaderInterface $reader): void
    {
        $tokenizer = new Tokenizer(new TokenizerConfig([
            'directories' => [__DIR__ . '/../../../../Fixtures/Fixtures6'],
            'exclude' => [],
        ]));

        $locator = $tokenizer->classLocator();

        $r = new Registry($this->dbal);

        $schema = (new Compiler())->compile($r, [
            new Embeddings(new TokenizerEmbeddingLocator($locator, $reader), $reader),
            new Entities(new TokenizerEntityLocator($locator, $reader), $reader),
            new ResetTables(),
            new MergeColumns($reader),
            new GenerateRelations(),
            new RenderTables(),
            new RenderRelations(),
            new MergeIndexes($reader),
            new SyncTables(),
            new GenerateTypecast(),
        ]);

        $this->assertArrayHasKey('address', $schema['user'][Schema::RELATIONS]);
        $this->assertSame(Relation::EMBEDDED, $schema['user'][Schema::RELATIONS]['address'][Relation::TYPE]);

        $this->assertSame('user:address:address', $schema['user'][Schema::RELATIONS]['address'][Relation::TARGET]);
        $this->assertSame(Relation::LOAD_EAGER, $schema['user'][Schema::RELATIONS]['address'][Relation::LOAD]);
        $this->assertTrue($r->getTableSchema($r->getEntity('user'))->hasIndex(['address_zipcode']));
    }

    /**
     * @dataProvider allReadersProvider
     */
    public function testRelationLazyLoad(ReaderInterface $reader): void
    {
        $tokenizer = new Tokenizer(new TokenizerConfig([
            'directories' => [__DIR__ . '/../../../../Fixtures/Fixtures7'],
            'exclude' => [],
        ]));

        $locator = $tokenizer->classLocator();

        $r = new Registry($this->dbal);

        $schema = (new Compiler())->compile($r, [
            new Embeddings(new TokenizerEmbeddingLocator($locator, $reader), $reader),
            new Entities(new TokenizerEntityLocator($locator, $reader), $reader),
            new ResetTables(),
            new MergeColumns($reader),
            new GenerateRelations(),
            new RenderTables(),
            new RenderRelations(),
            new MergeIndexes($reader),
            new SyncTables(),
            new GenerateTypecast(),
        ]);

        $this->assertArrayHasKey('address', $schema['user'][Schema::RELATIONS]);
        $this->assertSame(Relation::EMBEDDED, $schema['user'][Schema::RELATIONS]['address'][Relation::TYPE]);

        $this->assertSame('user:address:address', $schema['user'][Schema::RELATIONS]['address'][Relation::TARGET]);
        $this->assertSame(Relation::LOAD_PROMISE, $schema['user'][Schema::RELATIONS]['address'][Relation::LOAD]);
        $this->assertTrue($r->getTableSchema($r->getEntity('user:address:address'))->hasIndex(['address_zipcode']));
    }

    /**
     * @dataProvider allReadersProvider
     */
    public function testEmbeddedPrefix(ReaderInterface $reader): void
    {
        $tokenizer = new Tokenizer(new TokenizerConfig([
            'directories' => [__DIR__ . '/../../../../Fixtures/Fixtures6'],
            'exclude' => [],
        ]));

        $locator = $tokenizer->classLocator();

        $r = new Registry($this->dbal);

        $schema = (new Compiler())->compile($r, [
            new Embeddings(new TokenizerEmbeddingLocator($locator, $reader), $reader),
            new Entities(new TokenizerEntityLocator($locator, $reader), $reader),
            new ResetTables(),
            new MergeColumns($reader),
            new GenerateRelations(),
            new RenderTables(),
            new RenderRelations(),
            new MergeIndexes($reader),
            new SyncTables(),
            new GenerateTypecast(),
        ]);

        $address = [
            'city' => 'address_city',
            'country' => 'address_country',
            'address' => 'address_address',
            'zipcode' => 'address_zipcode',
            'id' => 'id',
        ];
        $workAddress = [
            'city' => 'work_address_city',
            'country' => 'work_address_country',
            'address' => 'work_address_address',
            'zipcode' => 'work_address_zipcode',
            'id' => 'id',
        ];

        $this->assertSame($address, $schema['user:address:address'][Schema::COLUMNS]);
        $this->assertSame($workAddress, $schema['user:address:workAddress'][Schema::COLUMNS]);
    }

    /**
     * @dataProvider allReadersProvider
     */
    public function testEmbeddedTypecast(ReaderInterface $reader): void
    {
        $tokenizer = new Tokenizer(new TokenizerConfig([
            'directories' => [__DIR__ . '/../../../../Fixtures/Fixtures26'],
            'exclude' => [],
        ]));

        $locator = $tokenizer->classLocator();

        $r = new Registry($this->dbal);

        $schema = (new Compiler())->compile($r, [
            new Embeddings(new TokenizerEmbeddingLocator($locator, $reader), $reader),
            new Entities(new TokenizerEntityLocator($locator, $reader), $reader),
            new ResetTables(),
            new MergeColumns($reader),
            new GenerateRelations(),
            new RenderTables(),
            new RenderRelations(),
            new MergeIndexes($reader),
            new SyncTables(),
            new GenerateTypecast(),
        ]);

        $this->assertSame(CityTypecast::class, $schema['user:address:address'][Schema::TYPECAST_HANDLER][0]);
    }
}

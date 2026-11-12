<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Common;

use Arcano\Annotated\Embeddings;
use Arcano\Annotated\Entities;
use Arcano\Annotated\Locator\TokenizerEmbeddingLocator;
use Arcano\Annotated\Locator\TokenizerEntityLocator;
use Arcano\Annotated\MergeColumns;
use Arcano\Annotated\MergeIndexes;
use Arcano\Annotated\Tests\Fixtures\Fixtures1\Child;
use Arcano\Annotated\Tests\Fixtures\Fixtures1\Simple;
use Arcano\Annotated\Tests\Fixtures\Fixtures1\Third;
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

abstract class ChildTestCase extends BaseTestCase
{
    //  /**
    //   * @dataProvider allReadersProvider
    //   */
    // public function testSimpleSchema(ReaderInterface $reader): void
    // {
    //     $r = new Registry($this->dbal);
    //     (new Entities($this->locator, $reader))->run($r);
    //
    //     $this->assertTrue($r->hasEntity(Simple::class));
    //     $this->assertTrue($r->hasEntity('simple'));
    //
    //     $this->assertTrue($r->getEntity('simple')->getFields()->has('id'));
    //
    //     $this->assertTrue($r->getEntity('simple')->getFields()->has('name'));
    //     $this->assertTrue($r->getEntity('simple')->getFields()->has('email'));
    //
    //     $schema = (new Compiler())->compile($r);
    //
    //     $this->assertSame([Schema::ROLE => 'simple'], $schema[Child::class]);
    //     $this->assertSame([Schema::ROLE => 'simple'], $schema[Third::class]);
    // }

    /**
     * @dataProvider allReadersProvider
     */
    public function testRelationToChild(ReaderInterface $reader): void
    {
        $tokenizer = new Tokenizer(new TokenizerConfig([
            'directories' => [__DIR__ . '/../../../Fixtures/Fixtures8'],
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

        $this->assertArrayHasKey('article', $schema['some'][Schema::RELATIONS]);
        $this->assertSame(Relation::HAS_ONE, $schema['some'][Schema::RELATIONS]['article'][Relation::TYPE]);

        $this->assertSame('post', $schema['some'][Schema::RELATIONS]['article'][Relation::TARGET]);
    }
}

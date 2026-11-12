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
use Arcano\Annotated\Tests\Fixtures\Fixtures17\MapperSegmentSchemaModifier;
use Arcano\Annotated\Tests\Fixtures\Fixtures17\ParentSegmentSchemaModifier;
use Arcano\ORM\SchemaInterface;
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

abstract class SchemaModifiersTestCase extends BaseTestCase
{
    /**
     * @dataProvider allReadersProvider
     */
    public function testModifierAnnotationShouldBeAddedToEntity(ReaderInterface $reader): void
    {
        $tokenizer = new Tokenizer(
            new TokenizerConfig([
                'directories' => [__DIR__ . '/../../../Fixtures/Fixtures17'],
                'exclude' => [],
            ]),
        );

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

        $post = $r->getEntity('post');

        $modifiers = iterator_to_array($post->getSchemaModifiers());
        $this->assertCount(2, $modifiers);
        $this->assertInstanceOf(ParentSegmentSchemaModifier::class, $modifiers[0]);
        $this->assertInstanceOf(MapperSegmentSchemaModifier::class, $modifiers[1]);

        $this->assertSame(\stdClass::class, $schema['post'][SchemaInterface::PARENT]);
        $this->assertSame(Child::class, $schema['post'][SchemaInterface::MAPPER]);
    }
}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Common;

use Arcano\Annotated\Embeddings;
use Arcano\Annotated\Locator\TokenizerEmbeddingLocator;
use Arcano\Annotated\Locator\TokenizerEntityLocator;
use Arcano\Annotated\MergeIndexes;
use Arcano\Annotated\Tests\Fixtures\Fixtures1\Typecast\Typecaster;
use Arcano\Annotated\Tests\Fixtures\Fixtures1\Typecast\UuidTypecaster;
use Arcano\Annotated\Tests\Fixtures\Fixtures19\BackedEnumWrapper;
use Arcano\ORM\Schema;
use Arcano\ORM\SchemaInterface;
use Arcano\Schema\Compiler;
use Arcano\Schema\Generator\GenerateRelations;
use Arcano\Schema\Generator\GenerateTypecast;
use Arcano\Schema\Generator\RenderRelations;
use Arcano\Schema\Generator\RenderTables;
use Arcano\Schema\Generator\ResetTables;
use Arcano\Schema\Generator\SyncTables;
use Arcano\Schema\Registry;
use Arcano\Annotated\Entities;
use Arcano\Annotated\MergeColumns;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\Tokenizer;
use Arcano\Annotated\Tests\Fixtures\Fixtures19\BackedEnum;

abstract class TypecastTestCase extends BaseTestCase
{
    /**
     * @dataProvider allReadersProvider
     */
    public function testEntityWithDefinedTypecastAsString(ReaderInterface $reader): void
    {
        $r = new Registry($this->dbal);

        $schema = (new Compiler())->compile($r, [
            new Entities(new TokenizerEntityLocator($this->locator, $reader), $reader),
            new MergeColumns($reader),
        ]);

        $this->assertSame(
            Typecaster::class,
            $schema['simple'][Schema::TYPECAST_HANDLER],
        );
    }

    /**
     * @dataProvider allReadersProvider
     */
    public function testEntityWithDefinedTypecastAsArray(ReaderInterface $reader): void
    {
        $r = new Registry($this->dbal);

        $schema = (new Compiler())->compile($r, [
            new Entities(new TokenizerEntityLocator($this->locator, $reader), $reader),
            new MergeColumns($reader),
        ]);

        $this->assertSame(
            [
                Typecaster::class,
                UuidTypecaster::class,
                'foo',
            ],
            $schema['tag'][Schema::TYPECAST_HANDLER],
        );
    }

    /**
     * @dataProvider allReadersProvider
     */
    public function testBackedEnum(ReaderInterface $reader): void
    {
        $tokenizer = new Tokenizer(new TokenizerConfig([
            'directories' => [__DIR__ . '/../../../Fixtures/Fixtures19'],
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

        $this->assertSame(
            [
                'bid' => 'int',
                'be' => BackedEnum::class,
                'bew' => [BackedEnumWrapper::class, 'typecast'],
            ],
            $schema['booking'][SchemaInterface::TYPECAST],
        );
    }
}

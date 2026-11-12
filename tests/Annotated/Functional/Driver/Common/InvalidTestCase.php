<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Common;

use Arcano\Annotated\Entities;
use Arcano\Annotated\Exception\AnnotationException;
use Arcano\Annotated\Locator\TokenizerEntityLocator;
use Arcano\Annotated\MergeColumns;
use Arcano\Annotated\MergeIndexes;
use Arcano\Schema\Compiler;
use Arcano\Schema\Exception\RelationException;
use Arcano\Schema\Generator\GenerateRelations;
use Arcano\Schema\Generator\GenerateTypecast;
use Arcano\Schema\Generator\RenderRelations;
use Arcano\Schema\Generator\RenderTables;
use Arcano\Schema\Generator\ResetTables;
use Arcano\Schema\Generator\SyncTables;
use Arcano\Schema\Generator\ValidateEntities;
use Arcano\Schema\Registry;
use Spiral\Attributes\AnnotationReader;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\Tokenizer;

abstract class InvalidTestCase extends BaseTestCase
{
    /**
     * @dataProvider allReadersProvider
     */
    public function testInvalidRelation(ReaderInterface $reader): void
    {
        $tokenizer = new Tokenizer(new TokenizerConfig([
            'directories' => [__DIR__ . '/../../../Fixtures/Fixtures3'],
            'exclude' => [],
        ]));

        $locator = $tokenizer->classLocator();

        $r = new Registry($this->dbal);

        $this->expectException(RelationException::class);

        (new Compiler())->compile($r, [
            new Entities(new TokenizerEntityLocator($locator, $reader), $reader),
            new ResetTables(),
            new MergeColumns($reader),
            new GenerateRelations(),
            new ValidateEntities(),
            new RenderTables(),
            new RenderRelations(),
            new MergeIndexes($reader),
            new SyncTables(),
            new GenerateTypecast(),
        ]);
    }

    /**
     * @dataProvider allReadersProvider
     */
    public function testNotDefinedColumnTypeShouldThrowAnException(ReaderInterface $reader): void
    {
        $message = $reader instanceof AnnotationReader
            ? 'Too few arguments to function Arcano\Annotated\Annotation\Column::__construct(), 0 passed'
            : 'Some of required arguments [`type`] is missed on `Arcano\Annotated\Tests\Fixtures\Fixtures4\User.id.`';

        $this->expectException(AnnotationException::class);
        $this->expectExceptionMessage($message);

        $tokenizer = new Tokenizer(new TokenizerConfig([
            'directories' => [__DIR__ . '/../../../Fixtures/Fixtures4'],
            'exclude' => [],
        ]));

        $locator = $tokenizer->classLocator();

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
            new SyncTables(),
            new GenerateTypecast(),
        ]);
    }
}

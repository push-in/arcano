<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Common\Relation\Morphed;

use Arcano\Annotated\Entities;
use Arcano\Annotated\Locator\TokenizerEntityLocator;
use Arcano\Annotated\MergeColumns;
use Arcano\Annotated\MergeIndexes;
use Arcano\Annotated\Tests\Fixtures\Fixtures1\Constrain\SomeConstrain;
use Arcano\Annotated\Tests\Fixtures\Fixtures1\LabelledInterface;
use Arcano\Annotated\Tests\Functional\Driver\Common\BaseTestCase;
use Arcano\ORM\Relation;
use Arcano\ORM\SchemaInterface as Schema;
use Arcano\Schema\Compiler;
use Arcano\Schema\Generator\GenerateRelations;
use Arcano\Schema\Generator\GenerateTypecast;
use Arcano\Schema\Generator\RenderRelations;
use Arcano\Schema\Generator\RenderTables;
use Arcano\Schema\Generator\ResetTables;
use Arcano\Schema\Generator\SyncTables;
use Arcano\Schema\Registry;
use Spiral\Attributes\ReaderInterface;

abstract class BelongsToMorphedTestCase extends BaseTestCase
{
    /**
     * @dataProvider allReadersProvider
     */
    public function testRelation(ReaderInterface $reader): void
    {
        $r = new Registry($this->dbal);

        $schema = (new Compiler())->compile($r, [
            new Entities(new TokenizerEntityLocator($this->locator, $reader), $reader),
            new ResetTables(),
            new MergeColumns($reader),
            new GenerateRelations(),
            new RenderTables(),
            new RenderRelations(),
            new MergeIndexes($reader),
            new SyncTables(),
            new GenerateTypecast(),
        ]);

        $this->assertArrayHasKey('owner', $schema['label'][Schema::RELATIONS]);
        $this->assertSame(
            Relation::BELONGS_TO_MORPHED,
            $schema['label'][Schema::RELATIONS]['owner'][Relation::TYPE],
        );

        $this->assertSame(
            LabelledInterface::class,
            $schema['label'][Schema::RELATIONS]['owner'][Relation::TARGET],
        );

        $this->assertSame(SomeConstrain::class, $schema['label'][Schema::SCOPE]);
    }
}

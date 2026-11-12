<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Common\Relation\Morphed;

use Arcano\Annotated\Entities;
use Arcano\Annotated\Locator\TokenizerEntityLocator;
use Arcano\Annotated\MergeColumns;
use Arcano\Annotated\MergeIndexes;
use Arcano\Annotated\Tests\Fixtures\RefersToMorphed\MorphedParentInterface;
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
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\Tokenizer;

abstract class RefersToMorphedTestCase extends BaseTestCase
{
    /**
     * @dataProvider allReadersProvider
     */
    public function testRelation(ReaderInterface $reader): void
    {
        $locator = (new Tokenizer(new TokenizerConfig([
            'directories' => [__DIR__ . '/../../../../../Fixtures/RefersToMorphed'],
            'exclude' => [],
        ])))->classLocator();

        $r = new Registry($this->dbal);

        $schema = (new Compiler())->compile($r, [
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

        $this->assertArrayHasKey('parent', $schema['comment'][Schema::RELATIONS]);
        $this->assertSame(
            Relation::REFERS_TO_MORPHED,
            $schema['comment'][Schema::RELATIONS]['parent'][Relation::TYPE],
        );
        $this->assertSame(
            MorphedParentInterface::class,
            $schema['comment'][Schema::RELATIONS]['parent'][Relation::TARGET],
        );

        // Morphed refers-to stores the outer key and the target role on the source entity.
        $this->assertContains('parent_id', $schema['comment'][Schema::COLUMNS]);
        $this->assertContains('parent_role', $schema['comment'][Schema::COLUMNS]);
    }
}

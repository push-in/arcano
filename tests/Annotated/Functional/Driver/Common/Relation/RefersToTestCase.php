<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Common\Relation;

use Arcano\Annotated\Entities;
use Arcano\Annotated\Locator\TokenizerEntityLocator;
use Arcano\Annotated\MergeColumns;
use Arcano\Annotated\MergeIndexes;
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

abstract class RefersToTestCase extends BaseTestCase
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

        $this->assertArrayHasKey('parent', $schema['simple'][Schema::RELATIONS]);
        $this->assertSame(Relation::REFERS_TO, $schema['simple'][Schema::RELATIONS]['parent'][Relation::TYPE]);
        $this->assertSame('simple', $schema['simple'][Schema::RELATIONS]['parent'][Relation::TARGET]);

        $this->assertSame(
            'NO ACTION',
            $this->dbal->database('default')
                ->getDriver()
                ->getSchema('simples')
                ->foreignKey(['parent_id'])
                ->getDeleteRule(),
        );
    }
}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Common\Relation\Morphed;

use Arcano\Annotated\Entities;
use Arcano\Annotated\Locator\TokenizerEntityLocator;
use Arcano\Annotated\MergeColumns;
use Arcano\Annotated\MergeIndexes;
use Arcano\Annotated\Tests\Fixtures\Fixtures1\Collection\BaseCollection;
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

abstract class MorphedHasManyTestCase extends BaseTestCase
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

        $this->assertArrayHasKey('labels', $schema['simple'][Schema::RELATIONS]);
        $this->assertSame(
            Relation::MORPHED_HAS_MANY,
            $schema['simple'][Schema::RELATIONS]['labels'][Relation::TYPE],
        );
        $this->assertSame('label', $schema['simple'][Schema::RELATIONS]['labels'][Relation::TARGET]);

        $this->assertArrayHasKey('labels', $schema['withTable'][Schema::RELATIONS]);
        $this->assertSame(
            Relation::MORPHED_HAS_MANY,
            $schema['withTable'][Schema::RELATIONS]['labels'][Relation::TYPE],
        );

        $this->assertSame('label', $schema['withTable'][Schema::RELATIONS]['labels'][Relation::TARGET]);

        $this->assertTrue(
            $this->dbal->database('default')
                ->getDriver()
                ->getSchema('labels')
                ->hasColumn('owner_id'),
        );

        $this->assertTrue(
            $this->dbal->database('default')
                ->getDriver()
                ->getSchema('labels')
                ->hasColumn('owner_role'),
        );

        $this->assertFalse(
            $this->dbal->database('default')
                ->getDriver()
                ->getSchema('labels')
                ->hasIndex(['owner_id', 'owner_role']),
        );

        $this->assertSame(
            BaseCollection::class,
            $schema['simple'][\Arcano\ORM\SchemaInterface::RELATIONS]['labels'][Relation::SCHEMA][Relation::COLLECTION_TYPE], // phpcs:ignore
        );
    }
}

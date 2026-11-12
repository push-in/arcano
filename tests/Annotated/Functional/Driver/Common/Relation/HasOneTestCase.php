<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Common\Relation;

use Arcano\Annotated\Entities;
use Arcano\Annotated\Locator\TokenizerEntityLocator;
use Arcano\Annotated\MergeColumns;
use Arcano\Annotated\MergeIndexes;
use Arcano\Annotated\Tests\Functional\Driver\Common\BaseTestCase;
use Arcano\ORM\Relation;
use Arcano\ORM\SchemaInterface;
use Arcano\Schema\Compiler;
use Arcano\Schema\Generator\GenerateRelations;
use Arcano\Schema\Generator\GenerateTypecast;
use Arcano\Schema\Generator\RenderRelations;
use Arcano\Schema\Generator\RenderTables;
use Arcano\Schema\Generator\ResetTables;
use Arcano\Schema\Generator\SyncTables;
use Arcano\Schema\Registry;
use Spiral\Attributes\AttributeReader;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\Tokenizer;

abstract class HasOneTestCase extends BaseTestCase
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

        $this->assertArrayHasKey('one', $schema['simple'][SchemaInterface::RELATIONS]);
        $this->assertSame(Relation::HAS_ONE, $schema['simple'][SchemaInterface::RELATIONS]['one'][Relation::TYPE]);
        $this->assertSame('eComplete', $schema['simple'][SchemaInterface::RELATIONS]['one'][Relation::TARGET]);
        $this->assertSame(Relation::LOAD_PROMISE, $schema['simple'][SchemaInterface::RELATIONS]['one'][Relation::LOAD]);
    }

    public function testInnerOuterKeys(): void
    {
        $tokenizer = new Tokenizer(
            new TokenizerConfig([
                'directories' => [__DIR__ . '/../../../../Fixtures/Fixtures18'],
                'exclude' => [],
            ]),
        );
        $reader = new AttributeReader();

        $locator = $tokenizer->classLocator();

        $r = new Registry($this->dbal);

        $schema = (new Compiler())->compile($r, [
            new Entities(new TokenizerEntityLocator($locator, $reader), $reader),
            new MergeColumns($reader),
            new GenerateRelations(),
            $t = new RenderTables(),
            new RenderRelations(),
            new MergeIndexes($reader),
            new GenerateTypecast(),
        ]);

        // RENDER!
        $t->getReflector()->run();

        $checks = [
            /** @see \Arcano\Annotated\Tests\Fixtures\Fixtures18\Booking::$reservation0 */
            ['reservation0', 'bid', 'booking_bid'],
            /** @see \Arcano\Annotated\Tests\Fixtures\Fixtures18\Booking::$reservation1 */
            ['reservation1', 'reserv_id', 'rid'],
            /** @see \Arcano\Annotated\Tests\Fixtures\Fixtures18\Booking::$reservation2 */
            ['reservation2', 'reserv_id', 'rid'],
        ];

        foreach ($checks as [$name, $innerKey, $outerKey]) {
            $relation = $schema['booking'][SchemaInterface::RELATIONS][$name];

            $this->assertSame(Relation::HAS_ONE, $relation[Relation::TYPE], "$name: relation type");
            $this->assertSame(
                (array) $innerKey,
                (array) $relation[Relation::SCHEMA][Relation::INNER_KEY],
                "$name: Inner Key",
            );
            $this->assertSame(
                (array) $outerKey,
                (array) $relation[Relation::SCHEMA][Relation::OUTER_KEY],
                "$name: Outer Key",
            );
        }
        $this->assertArrayNotHasKey(
            'id_reservation',
            $schema['booking'][SchemaInterface::COLUMNS],
        );
    }
}

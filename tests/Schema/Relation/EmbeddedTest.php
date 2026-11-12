<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Relation;

use Arcano\ORM\Schema;
use Arcano\Schema\Compiler;
use Arcano\Schema\Definition\Field;
use Arcano\Schema\Definition\Relation;
use Arcano\Schema\Exception\FieldException\EmbeddedPrimaryKeyException;
use Arcano\Schema\Exception\RegistryException;
use Arcano\Schema\Generator\GenerateRelations;
use Arcano\Schema\Generator\RenderRelations;
use Arcano\Schema\Generator\RenderTables;
use Arcano\Schema\Registry;
use Arcano\Schema\Relation\Embedded;
use Arcano\Schema\Tests\BaseTest;
use Arcano\Schema\Tests\Fixtures\Composite;
use Arcano\Schema\Tests\Fixtures\EmbeddedEntity;

abstract class EmbeddedTest extends BaseTest
{
    public function testGenerate(): void
    {
        $c = Composite::define();
        $e = EmbeddedEntity::define();

        $c->getRelations()->set(
            'embedded',
            (new Relation())->setTarget('embedded')->setType('embedded'),
        );

        $r = new Registry($this->dbal);
        $r->register($c)->linkTable($c, 'default', 'composite');
        $r->register($e);

        (new GenerateRelations(['embedded' => new Embedded()]))->run($r);

        $this->assertInstanceOf(Embedded::class, $r->getRelation($c, 'embedded'));
    }

    public function testThrowAnExceptionWhenPkNotDefinedInSource(): void
    {
        $this->expectException(RegistryException::class);
        $this->expectExceptionMessage('Entity `composite` must have defined primary key');

        $c = Composite::defineWithoutPk();
        $e = EmbeddedEntity::define();

        $c->getRelations()->set(
            'embedded',
            (new Relation())->setTarget('embedded')->setType('embedded'),
        );

        $r = new Registry($this->dbal);
        $r->register($c)->linkTable($c, 'default', 'composite');
        $r->register($e);

        (new GenerateRelations(['embedded' => new Embedded()]))->run($r);
    }

    public function testPackSchema(): void
    {
        $cc = new Compiler();

        $c = Composite::define();
        $e = EmbeddedEntity::define();

        $c->getRelations()->set(
            'embedded',
            (new Relation())->setTarget('embedded')->setType('embedded'),
        );

        $r = new Registry($this->dbal);
        $r->register($c)->linkTable($c, 'default', 'composite');
        $r->register($e);

        (new GenerateRelations(['embedded' => new Embedded()]))->run($r);
        $schema = $cc->compile($r);

        $this->assertArrayHasKey('composite', $schema);
        $this->assertArrayHasKey('embedded', $schema['composite'][Schema::RELATIONS]);
        $this->assertSame(
            \Arcano\ORM\Relation::EMBEDDED,
            $schema['composite'][Schema::RELATIONS]['embedded'][\Arcano\ORM\Relation::TYPE],
        );

        $this->assertSame(
            \Arcano\ORM\Relation::LOAD_EAGER,
            $schema['composite'][Schema::RELATIONS]['embedded'][\Arcano\ORM\Relation::LOAD],
        );

        $this->assertArrayHasKey('composite:embedded:embedded', $schema);
        $this->assertSame(['p_id'], $schema['composite:embedded:embedded'][Schema::PRIMARY_KEY]);
        $this->assertSame('default', $schema['composite:embedded:embedded'][Schema::DATABASE]);
        $this->assertSame('composite', $schema['composite:embedded:embedded'][Schema::TABLE]);

        $this->assertSame([
            'p_embedded' => 'embedded_column',
            'p_id' => 'id',
        ], $schema['composite:embedded:embedded'][Schema::COLUMNS]);
    }

    public function testPackSchemaLazyLoad(): void
    {
        $cc = new Compiler();

        $c = Composite::define();
        $e = EmbeddedEntity::define();

        $c->getRelations()->set(
            'embedded',
            (new Relation())->setTarget('embedded')->setType('embedded'),
        );

        $c->getRelations()->get('embedded')->getOptions()->set('load', 'lazy');

        $r = new Registry($this->dbal);
        $r->register($c)->linkTable($c, 'default', 'composite');
        $r->register($e);

        (new GenerateRelations(['embedded' => new Embedded()]))->run($r);
        $schema = $cc->compile($r);

        $this->assertArrayHasKey('composite', $schema);
        $this->assertArrayHasKey('embedded', $schema['composite'][Schema::RELATIONS]);
        $this->assertSame(
            \Arcano\ORM\Relation::EMBEDDED,
            $schema['composite'][Schema::RELATIONS]['embedded'][\Arcano\ORM\Relation::TYPE],
        );

        $this->assertSame(
            \Arcano\ORM\Relation::LOAD_PROMISE,
            $schema['composite'][Schema::RELATIONS]['embedded'][\Arcano\ORM\Relation::LOAD],
        );

        $this->assertArrayHasKey('composite:embedded:embedded', $schema);
        $this->assertSame(['p_id'], $schema['composite:embedded:embedded'][Schema::PRIMARY_KEY]);
        $this->assertSame('default', $schema['composite:embedded:embedded'][Schema::DATABASE]);
        $this->assertSame('composite', $schema['composite:embedded:embedded'][Schema::TABLE]);

        $this->assertSame([
            'p_embedded' => 'embedded_column',
            'p_id' => 'id',
        ], $schema['composite:embedded:embedded'][Schema::COLUMNS]);
    }

    public function testRenderTable(): void
    {
        $c = Composite::define();
        $e = EmbeddedEntity::define();

        $c->getRelations()->set(
            'embedded',
            (new Relation())->setTarget('embedded')->setType('embedded'),
        );

        $c->getRelations()->get('embedded')->getOptions()->set('load', 'lazy');

        $r = new Registry($this->dbal);
        $r->register($c)->linkTable($c, 'default', 'composite');
        $r->register($e);

        (new Compiler())->compile($r, [
            new GenerateRelations(['embedded' => new Embedded()]),
            $t = new RenderTables(),
            new RenderRelations(),
        ]);

        // RENDER!
        $t->getReflector()->run();

        $table = $this->getDriver()->getSchema('composite');
        $this->assertTrue($table->exists());
        $this->assertTrue($table->hasColumn('id'));
        $this->assertTrue($table->hasColumn('embedded_column'));
    }

    public function testEmbedIdFieldWithPrefix(): void
    {
        $c = Composite::define();
        $e = EmbeddedEntity::define();

        $e->getFields()->set('p_id', (new Field())->setColumn('embedded_id')->setType('int'));

        $c->getRelations()->set(
            'embedded',
            (new Relation())->setTarget('embedded')->setType('embedded'),
        );

        $r = new Registry($this->dbal);
        $r->register($c)->linkTable($c, 'default', 'composite');
        $r->register($e);

        $this->expectException(EmbeddedPrimaryKeyException::class);
        $this->expectExceptionMessage('Entity `composite:embedded:embedded` has conflicted field `p_id`.');

        (new Compiler())->compile(
            $r,
            [
                new GenerateRelations(['embedded' => new Embedded()]),
                $t = new RenderTables(),
                new RenderRelations(),
            ],
        );
    }

    public function testEmbeddedPrefix(): void
    {
        $c = Composite::define();
        $e = EmbeddedEntity::define();

        $c->getRelations()->set(
            'embedded',
            (new Relation())->setTarget('embedded')->setType('embedded'),
        );

        $c->getRelations()->get('embedded')->getOptions()->set('embeddedPrefix', 'prefix_');

        $r = new Registry($this->dbal);
        $r->register($c)->linkTable($c, 'default', 'composite');
        $r->register($e);

        (new Compiler())->compile($r, [
            new GenerateRelations(['embedded' => new Embedded()]),
            $t = new RenderTables(),
            new RenderRelations(),
        ]);

        $t->getReflector()->run();

        $table = $this->getDriver()->getSchema('composite');

        $this->assertTrue($table->hasColumn('id'));
        $this->assertTrue($table->hasColumn('prefix_embedded_column'));
    }
}

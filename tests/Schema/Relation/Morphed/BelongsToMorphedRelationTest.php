<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Relation\Morphed;

use Arcano\ORM\Relation;
use Arcano\ORM\Schema;
use Arcano\Schema\Compiler;
use Arcano\Schema\Exception\SchemaException;
use Arcano\Schema\Generator\GenerateRelations;
use Arcano\Schema\Generator\RenderRelations;
use Arcano\Schema\Generator\RenderTables;
use Arcano\Schema\Registry;
use Arcano\Schema\Relation\HasOne;
use Arcano\Schema\Relation\Morphed\BelongsToMorphed;
use Arcano\Schema\Relation\Morphed\MorphedHasMany;
use Arcano\Schema\Relation\Morphed\MorphedHasOne;
use Arcano\Schema\Tests\BaseTest;
use Arcano\Schema\Tests\Fixtures\Author;
use Arcano\Schema\Tests\Fixtures\In2;
use Arcano\Schema\Tests\Fixtures\MorphedTo;
use Arcano\Schema\Tests\Fixtures\Post;

abstract class BelongsToMorphedRelationTest extends BaseTest
{
    public function testGenerate(): void
    {
        $e = MorphedTo::define();
        $a = Author::define();
        $p = Post::define();

        $p->getRelations()->remove('author');

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'morphed');

        $r->register($a)->linkTable($a, 'default', 'author');
        $r->register($p)->linkTable($p, 'default', 'post');

        (new GenerateRelations(['belongsToMorphed' => new BelongsToMorphed()]))->run($r);

        $this->assertInstanceOf(BelongsToMorphed::class, $r->getRelation($e, 'parent'));
    }

    public function testGenerateInconsistentName(): void
    {
        $e = MorphedTo::define();
        $a = Author::define();
        $p = In2::define();

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'morphed');

        $r->register($a)->linkTable($a, 'default', 'author');
        $r->register($p)->linkTable($p, 'default', 'in2');

        $this->expectException(SchemaException::class);

        (new GenerateRelations(['belongsToMorphed' => new BelongsToMorphed()]))->run($r);
    }

    public function testPackSchema(): void
    {
        $e = MorphedTo::define();
        $a = Author::define();
        $p = Post::define();

        $p->getRelations()->remove('author');

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'morphed');

        $r->register($a)->linkTable($a, 'default', 'author');
        $r->register($p)->linkTable($p, 'default', 'post');

        $schema = (new Compiler())->compile($r, [
            new GenerateRelations(['belongsToMorphed' => new BelongsToMorphed()]),
        ]);

        $this->assertArrayHasKey('morphed', $schema);
        $this->assertSame(
            Relation::BELONGS_TO_MORPHED,
            $schema['morphed'][Schema::RELATIONS]['parent'][Relation::TYPE],
        );

        $this->assertArrayHasKey('morphed', $schema);
        $this->assertArrayHasKey('parent_p_id', $schema['morphed'][Schema::COLUMNS]);
        $this->assertArrayHasKey('parent_role', $schema['morphed'][Schema::COLUMNS]);
    }

    public function testRenderTable(): void
    {
        $e = MorphedTo::define();
        $a = Author::define();
        $p = Post::define();

        $p->getRelations()->remove('author');

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'morphed');

        $r->register($a)->linkTable($a, 'default', 'author');
        $r->register($p)->linkTable($p, 'default', 'post');

        (new Compiler())->compile($r, [
            new GenerateRelations(['belongsToMorphed' => new BelongsToMorphed()]),
            $t = new RenderTables(),
            new RenderRelations(),
        ]);

        // RENDER!
        $t->getReflector()->run();

        $table = $this->getDriver()->getSchema('morphed');
        $this->assertTrue($table->exists());
        $this->assertTrue($table->hasColumn('parent_p_id'));
        $this->assertTrue($table->hasColumn('parent_role'));

        $this->assertTrue($table->hasColumn('parent_p_id'));
        $this->assertTrue($table->column('parent_role')->getType() == 'string');
        $this->assertTrue($table->column('parent_role')->getSize() == 32);

        $this->assertTrue($table->hasIndex(['parent_p_id', 'parent_role']));
    }

    public function testInverseToInvalidType(): void
    {
        $e = MorphedTo::define();
        $a = Author::define();
        $p = Post::define();

        $p->getRelations()->remove('author');
        $e->getRelations()->get('parent')->setInverse('morphed', 'hasOne');

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'morphed');

        $r->register($a)->linkTable($a, 'default', 'author');
        $r->register($p)->linkTable($p, 'default', 'post');

        $this->expectException(SchemaException::class);

        (new Compiler())->compile($r, [
            new GenerateRelations([
                'belongsToMorphed' => new BelongsToMorphed(),
                'hasOne' => new HasOne(),
            ]),
        ]);
    }

    public function testInverseHasOne(): void
    {
        $e = MorphedTo::define();
        $a = Author::define();
        $p = Post::define();

        $p->getRelations()->remove('author');
        $e->getRelations()->get('parent')->setInverse('morphed', 'morphedHasOne');

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'morphed');

        $r->register($a)->linkTable($a, 'default', 'author');
        $r->register($p)->linkTable($p, 'default', 'post');

        $schema = (new Compiler())->compile($r, [
            new GenerateRelations([
                'belongsToMorphed' => new BelongsToMorphed(),
                'morphedHasOne' => new MorphedHasOne(),
            ]),
        ]);

        $this->assertArrayHasKey('morphed', $schema['author'][Schema::RELATIONS]);
        $this->assertSame(
            Relation::MORPHED_HAS_ONE,
            $schema['author'][Schema::RELATIONS]['morphed'][Relation::TYPE],
        );
        $this->assertSame(
            ['p_id'],
            $schema['author'][Schema::RELATIONS]['morphed'][Relation::SCHEMA][Relation::INNER_KEY],
        );
        $this->assertSame(
            'parent_role',
            $schema['author'][Schema::RELATIONS]['morphed'][Relation::SCHEMA][Relation::MORPH_KEY],
        );

        $this->assertArrayHasKey('morphed', $schema['post'][Schema::RELATIONS]);
        $this->assertSame(
            Relation::MORPHED_HAS_ONE,
            $schema['post'][Schema::RELATIONS]['morphed'][Relation::TYPE],
        );
        $this->assertSame(
            ['p_id'],
            $schema['post'][Schema::RELATIONS]['morphed'][Relation::SCHEMA][Relation::INNER_KEY],
        );
        $this->assertSame(
            'parent_role',
            $schema['post'][Schema::RELATIONS]['morphed'][Relation::SCHEMA][Relation::MORPH_KEY],
        );
    }

    public function testInverseHasMany(): void
    {
        $e = MorphedTo::define();
        $a = Author::define();
        $p = Post::define();

        $p->getRelations()->remove('author');
        $e->getRelations()->get('parent')->setInverse('morphed', 'morphedHasMany');

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'morphed');

        $r->register($a)->linkTable($a, 'default', 'author');
        $r->register($p)->linkTable($p, 'default', 'post');

        $schema = (new Compiler())->compile($r, [
            new GenerateRelations([
                'belongsToMorphed' => new BelongsToMorphed(),
                'morphedHasMany' => new MorphedHasMany(),
            ]),
        ]);

        $this->assertArrayHasKey('morphed', $schema['author'][Schema::RELATIONS]);
        $this->assertSame(
            Relation::MORPHED_HAS_MANY,
            $schema['author'][Schema::RELATIONS]['morphed'][Relation::TYPE],
        );
        $this->assertSame(
            ['p_id'],
            $schema['author'][Schema::RELATIONS]['morphed'][Relation::SCHEMA][Relation::INNER_KEY],
        );
        $this->assertSame(
            'parent_role',
            $schema['author'][Schema::RELATIONS]['morphed'][Relation::SCHEMA][Relation::MORPH_KEY],
        );

        $this->assertArrayHasKey('morphed', $schema['post'][Schema::RELATIONS]);
        $this->assertSame(
            Relation::MORPHED_HAS_MANY,
            $schema['post'][Schema::RELATIONS]['morphed'][Relation::TYPE],
        );
        $this->assertSame(
            ['p_id'],
            $schema['post'][Schema::RELATIONS]['morphed'][Relation::SCHEMA][Relation::INNER_KEY],
        );
        $this->assertSame(
            'parent_role',
            $schema['post'][Schema::RELATIONS]['morphed'][Relation::SCHEMA][Relation::MORPH_KEY],
        );
    }
}

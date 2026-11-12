<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Relation;

use Arcano\Database\Schema\AbstractTable;
use Arcano\ORM\Collection\ArrayCollectionFactory;
use Arcano\ORM\Relation;
use Arcano\ORM\Schema;
use Arcano\Schema\Compiler;
use Arcano\Schema\Definition\Entity;
use Arcano\Schema\Definition\Field;
use Arcano\Schema\Definition\Relation as RelationDefinition;
use Arcano\Schema\Exception\RegistryException;
use Arcano\Schema\Exception\SchemaException;
use Arcano\Schema\Generator\GenerateRelations;
use Arcano\Schema\Generator\RenderRelations;
use Arcano\Schema\Generator\RenderTables;
use Arcano\Schema\Registry;
use Arcano\Schema\Relation\BelongsTo;
use Arcano\Schema\Relation\HasMany;
use Arcano\Schema\Relation\ManyToMany;
use Arcano\Schema\Relation\RefersTo;
use Arcano\Schema\Tests\BaseTest;
use Arcano\Schema\Tests\Fixtures\Plain;
use Arcano\Schema\Tests\Fixtures\User;

abstract class HasManyRelationTest extends BaseTest
{
    public function testGenerate(): void
    {
        $e = Plain::define();
        $u = User::define();

        $u->getRelations()->get('plain')->setType('hasMany');

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'plain');
        $r->register($u)->linkTable($u, 'default', 'user');

        (new GenerateRelations(['hasMany' => new HasMany()]))->run($r);

        $this->assertInstanceOf(HasMany::class, $r->getRelation($u, 'plain'));
    }

    public function testThrowAnExceptionWhenPkNotDefinedInSource(): void
    {
        $this->expectException(RegistryException::class);
        $this->expectExceptionMessage('Entity `plain` must have defined primary key');

        $e = Plain::defineWithoutPK();
        $u = User::define();

        $u->getRelations()->get('plain')->setType('hasMany');

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'plain');
        $r->register($u)->linkTable($u, 'default', 'user');

        (new GenerateRelations(['hasMany' => new HasMany()]))->run($r);
    }

    public function testThrowAnExceptionWhenPkNotDefinedInTarget(): void
    {
        $this->expectException(RegistryException::class);
        $this->expectExceptionMessage('Entity `user` must have defined primary key');

        $e = Plain::define();
        $u = User::defineWithoutPK();

        $u->getRelations()->get('plain')->setType('hasMany');

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'plain');
        $r->register($u)->linkTable($u, 'default', 'user');

        (new GenerateRelations(['hasMany' => new HasMany()]))->run($r);
    }

    public function testPackSchema(): void
    {
        $c = new Compiler();

        $e = Plain::define();
        $u = User::define();

        $u->getRelations()
            ->get('plain')
            ->setType('hasMany')
            ->getOptions()->set('collection', ArrayCollectionFactory::class);

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'plain');
        $r->register($u)->linkTable($u, 'default', 'user');

        (new GenerateRelations(['hasMany' => new HasMany()]))->run($r);
        $schema = $c->compile($r);

        $this->assertArrayHasKey('user', $schema);
        $this->assertSame(Relation::HAS_MANY, $schema['user'][Schema::RELATIONS]['plain'][Relation::TYPE]);

        $this->assertArrayHasKey('plain', $schema['user'][Schema::RELATIONS]);

        $this->assertArrayHasKey('plain', $schema);
        $this->assertArrayHasKey('user_p_id', $schema['plain'][Schema::COLUMNS]);

        $this->assertSame(
            ArrayCollectionFactory::class,
            $schema['user'][Schema::RELATIONS]['plain'][Relation::SCHEMA][Relation::COLLECTION_TYPE],
        );
    }

    public function testCustomKey(): void
    {
        $e = Plain::define();
        $u = User::define();

        $u->getRelations()->get('plain')->setType('hasMany');

        $u->getRelations()->get('plain')->getOptions()->set('outerKey', 'parent_id');

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'plain');
        $r->register($u)->linkTable($u, 'default', 'user');

        $schema = (new Compiler())->compile($r, [
            new GenerateRelations(['hasMany' => new HasMany()]),
        ]);

        $this->assertArrayHasKey('user', $schema);
        $this->assertArrayHasKey('plain', $schema['user'][Schema::RELATIONS]);

        $this->assertArrayHasKey('plain', $schema);
        $this->assertArrayHasKey('parent_id', $schema['plain'][Schema::COLUMNS]);
    }

    public function testRenderTable(): void
    {
        $e = Plain::define();
        $u = User::define();

        $u->getRelations()->get('plain')->setType('hasMany');

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'plain');
        $r->register($u)->linkTable($u, 'default', 'user');

        (new Compiler())->compile($r, [
            new GenerateRelations(['hasMany' => new HasMany()]),
            $t = new RenderTables(),
            new RenderRelations(),
        ]);

        // RENDER!
        $t->getReflector()->run();

        $table = $this->getDriver()->getSchema('plain');
        $this->assertTrue($table->exists());
        $this->assertTrue($table->hasForeignKey(['user_p_id']));
    }

    public function testRenderTableRedefined(): void
    {
        $e = Plain::define();
        $u = User::define();

        $u->getRelations()->get('plain')->setType('hasMany');

        $u->getRelations()->get('plain')->getOptions()->set('outerKey', 'parent_id');
        $u->getRelations()->get('plain')->getOptions()->set('fkCreate', false);

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'plain');
        $r->register($u)->linkTable($u, 'default', 'user');

        (new Compiler())->compile($r, [
            new GenerateRelations(['hasMany' => new HasMany()]),
            $t = new RenderTables(),
            new RenderRelations(),
        ]);

        // RENDER!
        $t->getReflector()->run();

        $table = $this->getDriver()->getSchema('plain');
        $this->assertTrue($table->exists());
        $this->assertTrue($table->hasColumn('parent_id'));
        $this->assertFalse($table->hasForeignKey(['parent_id']));
    }

    public function testInverseInvalidType(): void
    {
        $c = new Compiler();

        $e = Plain::define();
        $u = User::define();

        $u->getRelations()->get('plain')->setType('hasMany')->setInverse('user', 'manyToMany');

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'plain');
        $r->register($u)->linkTable($u, 'default', 'user');

        $this->expectException(SchemaException::class);

        (new GenerateRelations([
            'hasMany' => new HasMany(),
            'manyToMany' => new ManyToMany(),
        ]))->run($r);
    }

    public function testInverseToBelongsTo(): void
    {
        $c = new Compiler();

        $e = Plain::define();
        $u = User::define();

        $u->getRelations()->get('plain')->setType('hasMany')->setInverse('user', 'belongsTo');

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'plain');
        $r->register($u)->linkTable($u, 'default', 'user');

        (new GenerateRelations([
            'hasMany' => new HasMany(),
            'belongsTo' => new BelongsTo(),
        ]))->run($r);
        $schema = $c->compile($r);

        $this->assertArrayHasKey('user', $schema['plain'][Schema::RELATIONS]);
        $this->assertSame(Relation::BELONGS_TO, $schema['plain'][Schema::RELATIONS]['user'][Relation::TYPE]);

        $this->assertSame(
            'user',
            $schema['plain'][Schema::RELATIONS]['user'][Relation::TARGET],
        );

        $this->assertSame(
            ['p_id'],
            $schema['plain'][Schema::RELATIONS]['user'][Relation::SCHEMA][Relation::OUTER_KEY],
        );

        $this->assertSame(
            'user_p_id',
            $schema['plain'][Schema::RELATIONS]['user'][Relation::SCHEMA][Relation::INNER_KEY],
        );
    }

    public function testInverseToRefersTo(): void
    {
        $c = new Compiler();

        $e = Plain::define();
        $u = User::define();

        $u->getRelations()->get('plain')->setType('hasMany')->setInverse('user', 'refersTo');

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'plain');
        $r->register($u)->linkTable($u, 'default', 'user');

        (new GenerateRelations([
            'hasMany' => new HasMany(),
            'refersTo' => new RefersTo(),
        ]))->run($r);
        $schema = $c->compile($r);

        $this->assertArrayHasKey('user', $schema['plain'][Schema::RELATIONS]);
        $this->assertSame(Relation::REFERS_TO, $schema['plain'][Schema::RELATIONS]['user'][Relation::TYPE]);

        $this->assertSame(
            'user',
            $schema['plain'][Schema::RELATIONS]['user'][Relation::TARGET],
        );

        $this->assertSame(
            ['p_id'],
            $schema['plain'][Schema::RELATIONS]['user'][Relation::SCHEMA][Relation::OUTER_KEY],
        );

        $this->assertSame(
            'user_p_id',
            $schema['plain'][Schema::RELATIONS]['user'][Relation::SCHEMA][Relation::INNER_KEY],
        );
    }

    public function testRenderWithIndex(): void
    {
        $e = Plain::define();
        $u = User::define();

        $u->getRelations()->get('plain')->setType('hasMany');

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'plain');
        $r->register($u)->linkTable($u, 'default', 'user');

        (new Compiler())->compile($r, [
            new GenerateRelations(['hasMany' => new HasMany()]),
            $t = new RenderTables(),
            new RenderRelations(),
        ]);

        $t->getReflector()->run();

        $table = $this->getDriver()->getSchema('plain');
        $this->assertTrue($table->hasIndex(['user_p_id']));
    }

    public function belongsToNullableOrders(): iterable
    {
        yield 'hasMany source first' => [true];
        yield 'belongsTo source first' => [false];
    }

    /**
     * @dataProvider belongsToNullableOrders
     */
    public function testNullableBelongsToMakesSharedColumnNullable(bool $userFirst): void
    {
        $e = Plain::define();
        $u = User::define();

        $u->getRelations()->get('plain')->setType('hasMany');
        $e->getRelations()->set(
            'user',
            (new RelationDefinition())->setTarget('user')->setType('belongsTo'),
        );
        $e->getRelations()->get('user')->getOptions()->set('nullable', true);

        $table = $this->renderPlainWithUser($e, $u, $userFirst);

        $this->assertTrue($table->column('user_p_id')->isNullable());
    }

    /**
     * @dataProvider belongsToNullableOrders
     */
    public function testNullableBelongsToKeepsUserDefinedColumn(bool $userFirst): void
    {
        $e = Plain::define();
        $u = User::define();

        $e->getFields()->set('user_p_id', (new Field())->setType('int')->setColumn('user_p_id'));
        $u->getRelations()->get('plain')->setType('hasMany');
        $e->getRelations()->set(
            'user',
            (new RelationDefinition())->setTarget('user')->setType('belongsTo'),
        );
        $e->getRelations()->get('user')->getOptions()->set('nullable', true);

        $table = $this->renderPlainWithUser($e, $u, $userFirst);

        $this->assertFalse($table->column('user_p_id')->isNullable());
    }

    private function renderPlainWithUser(Entity $plain, Entity $user, bool $userFirst): AbstractTable
    {
        $r = new Registry($this->dbal);
        $entities = $userFirst ? [[$user, 'user'], [$plain, 'plain']] : [[$plain, 'plain'], [$user, 'user']];
        foreach ($entities as [$entity, $table]) {
            $r->register($entity)->linkTable($entity, 'default', $table);
        }

        (new Compiler())->compile($r, [
            new GenerateRelations(['hasMany' => new HasMany(), 'belongsTo' => new BelongsTo()]),
            $t = new RenderTables(),
            new RenderRelations(),
        ]);

        $t->getReflector()->run();

        return $this->getDriver()->getSchema('plain');
    }
}

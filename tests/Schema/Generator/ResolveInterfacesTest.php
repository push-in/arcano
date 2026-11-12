<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Generator;

use Arcano\ORM\Schema;
use Arcano\Schema\Compiler;
use Arcano\Schema\Exception\RelationException;
use Arcano\Schema\Generator\GenerateRelations;
use Arcano\Schema\Generator\ResolveInterfaces;
use Arcano\Schema\Registry;
use Arcano\Schema\Relation\BelongsTo;
use Arcano\Schema\Tests\BaseTest;
use Arcano\Schema\Tests\Fixtures\Author;
use Arcano\Schema\Tests\Fixtures\AuthorInterface;
use Arcano\Schema\Tests\Fixtures\Post;
use Arcano\Schema\Tests\Fixtures\User;

abstract class ResolveInterfacesTest extends BaseTest
{
    public function testResolveInterfaceDependency(): void
    {
        $e = Post::define();
        $u = Author::define();

        $e->getRelations()->get('author')->setTarget(AuthorInterface::class);
        $e->getRelations()->get('author')->getOptions()->set(ResolveInterfaces::STATIC_LINK, true);

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'post');
        $r->register($u)->linkTable($u, 'default', 'author');

        $schema = (new Compiler())->compile($r, [
            new ResolveInterfaces(),
            new GenerateRelations(['belongsTo' => new BelongsTo()]),
        ]);

        $this->assertArrayHasKey('post', $schema);
        $this->assertArrayHasKey('author', $schema['post'][Schema::RELATIONS]);

        $this->assertArrayHasKey('author', $schema);
        $this->assertArrayHasKey('author_p_id', $schema['post'][Schema::COLUMNS]);
    }

    public function testUnableResolveInterfaceDependency(): void
    {
        $e = Post::define();
        $u = Author::define();

        $e->getRelations()->get('author')->setTarget(AuthorInterface::class);
        $e->getRelations()->get('author')->getOptions()->set(ResolveInterfaces::STATIC_LINK, true);

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'post');

        $this->expectException(RelationException::class);

        (new Compiler())->compile($r, [
            new ResolveInterfaces(),
            new GenerateRelations(['belongsTo' => new BelongsTo()]),
        ]);
    }

    public function testInvalidStaticLink(): void
    {
        $e = Post::define();
        $u = Author::define();

        $e->getRelations()->get('author')->setTarget('invalid');
        $e->getRelations()->get('author')->getOptions()->set(ResolveInterfaces::STATIC_LINK, true);

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'post');

        $this->expectException(RelationException::class);

        (new Compiler())->compile($r, [
            new ResolveInterfaces(),
            new GenerateRelations(['belongsTo' => new BelongsTo()]),
        ]);
    }

    public function testAmbiguousDependency(): void
    {
        $e = Post::define();
        $u = Author::define();
        $u1 = User::define();

        $e->getRelations()->get('author')->setTarget(AuthorInterface::class);
        $e->getRelations()->get('author')->getOptions()->set(ResolveInterfaces::STATIC_LINK, true);

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'post');
        $r->register($u)->linkTable($u, 'default', 'author');
        $r->register($u1)->linkTable($u1, 'default', 'user');

        $this->expectException(RelationException::class);

        (new Compiler())->compile($r, [
            new ResolveInterfaces(),
            new GenerateRelations(['belongsTo' => new BelongsTo()]),
        ]);
    }
}

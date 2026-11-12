<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\Postgres;

use Arcano\Schema\Compiler;
use Arcano\Schema\Definition\Relation as RelationDefinition;
use Arcano\Schema\Generator\GenerateRelations;
use Arcano\Schema\Generator\RenderRelations;
use Arcano\Schema\Generator\RenderTables;
use Arcano\Schema\Registry;
use Arcano\Schema\Relation\ManyToMany;
use Arcano\Schema\Tests\Fixtures\Post;
use Arcano\Schema\Tests\Fixtures\Tag;
use Arcano\Schema\Tests\Fixtures\TagContext;
use Arcano\Schema\Tests\Relation\ManyToManyRelationTest as BaseTest;

/**
 * @group driver
 * @group driver-postgres
 */
class ManyToManyRelationTest extends BaseTest
{
    public const DRIVER = 'postgres';

    public function testRenderWithoutIndex(): void
    {
        $post = Post::define();
        $tag = Tag::define();
        $tagContext = TagContext::define();

        $post->getRelations()->remove('author');

        $post->getRelations()->set('tags', new RelationDefinition());
        $post->getRelations()->get('tags')
            ->setType('manyToMany')
            ->setTarget('tag')
            ->getOptions()
            ->set('through', 'tagContext')
            ->set('indexCreate', false);

        $r = new Registry($this->dbal);
        $r->register($post)->linkTable($post, 'default', 'post');
        $r->register($tag)->linkTable($tag, 'default', 'tag');
        $r->register($tagContext)->linkTable($tagContext, 'default', 'tag_context');

        (new Compiler())->compile($r, [
            new GenerateRelations(['manyToMany' => new ManyToMany()]),
            $t = new RenderTables(),
            new RenderRelations(),
        ]);

        $t->getReflector()->run();

        $table = $this->getDriver()->getSchema('tag_context');
        $this->assertFalse($table->hasIndex(['tag_p_id']));
        $this->assertFalse($table->hasIndex(['post_p_id']));
    }
}

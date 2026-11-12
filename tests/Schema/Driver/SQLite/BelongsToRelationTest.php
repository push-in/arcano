<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\SQLite;

use Arcano\Schema\Compiler;
use Arcano\Schema\Generator\GenerateRelations;
use Arcano\Schema\Generator\RenderRelations;
use Arcano\Schema\Generator\RenderTables;
use Arcano\Schema\Registry;
use Arcano\Schema\Relation\BelongsTo;
use Arcano\Schema\Tests\Fixtures\Author;
use Arcano\Schema\Tests\Fixtures\Post;
use Arcano\Schema\Tests\Relation\BelongsToRelationTest as BaseTest;

/**
 * @group driver
 * @group driver-sqlite
 */
class BelongsToRelationTest extends BaseTest
{
    public const DRIVER = 'sqlite';

    public function testRenderWithoutIndex(): void
    {
        $e = Post::define();
        $u = Author::define();

        $e->getRelations()->get('author')->getOptions()->set('indexCreate', false);

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'post');
        $r->register($u)->linkTable($u, 'default', 'author');

        (new Compiler())->compile($r, [
            new GenerateRelations(['belongsTo' => new BelongsTo()]),
            $t = new RenderTables(),
            new RenderRelations(),
        ]);

        $t->getReflector()->run();

        $table = $this->getDriver()->getSchema('post');
        $this->assertFalse($table->hasIndex(['author_p_id']));
    }
}

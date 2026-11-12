<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Driver\Postgres;

use Arcano\Schema\Compiler;
use Arcano\Schema\Generator\GenerateRelations;
use Arcano\Schema\Generator\RenderRelations;
use Arcano\Schema\Generator\RenderTables;
use Arcano\Schema\Registry;
use Arcano\Schema\Relation\HasOne;
use Arcano\Schema\Tests\Fixtures\Plain;
use Arcano\Schema\Tests\Fixtures\User;
use Arcano\Schema\Tests\Relation\HasOneRelationTest as BaseTest;

/**
 * @group driver
 * @group driver-postgres
 */
class HasOneRelationTest extends BaseTest
{
    public const DRIVER = 'postgres';

    public function testRenderWithoutIndex(): void
    {
        $e = Plain::define();
        $u = User::define();

        $u->getRelations()->get('plain')->getOptions()->set('indexCreate', false);

        $r = new Registry($this->dbal);
        $r->register($e)->linkTable($e, 'default', 'plain');
        $r->register($u)->linkTable($u, 'default', 'user');

        (new Compiler())->compile($r, [
            new GenerateRelations(['hasOne' => new HasOne()]),
            $t = new RenderTables(),
            new RenderRelations(),
        ]);

        $t->getReflector()->run();

        $table = $this->getDriver()->getSchema('plain');
        $this->assertFalse($table->hasIndex(['user_p_id']));
    }
}

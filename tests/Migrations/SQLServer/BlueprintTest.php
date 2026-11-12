<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Arcano\Migrations\Tests\SQLServer;

use Arcano\Migrations\Capsule;
use Arcano\Migrations\TableBlueprint;

/**
 * @group driver
 * @group driver-sqlserver
 */
class BlueprintTest extends \Arcano\Migrations\Tests\BlueprintTest
{
    public const DRIVER = 'sqlserver';

    public function testCreateForeignWithoutIndex(): void
    {
        $blueprint = new TableBlueprint(new Capsule($this->db), 'sample1');

        $blueprint->addColumn('id', 'primary')->create();
        $blueprint = new TableBlueprint(new Capsule($this->db), 'sample');

        $blueprint->addColumn('id', 'primary')
            ->addColumn('sample_id', 'int')
            ->addForeignKey(['sample_id'], 'sample1', ['id'], ['indexCreate' => false])
            ->create();

        $this->assertFalse($blueprint->getSchema()->hasIndex(['sample_id']));
    }
}

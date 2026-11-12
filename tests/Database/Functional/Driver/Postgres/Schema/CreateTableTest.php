<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\Postgres\Schema;

// phpcs:ignore
use Arcano\Database\Tests\Functional\Driver\Common\Schema\CreateTableTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class CreateTableTest extends CommonClass
{
    public const DRIVER = 'postgres';

    public function testCreateAndDrop(): void
    {
        $schema = $this->schema('table');
        $this->assertFalse($schema->exists());

        $schema->primary('id');
        $schema->save();

        $this->assertSame('public.table', $schema->column('id')->getTable());

        $this->assertTrue($schema->exists());

        $schema->declareDropped();
        $schema->save();

        $schema = $this->schema('table');
        $this->assertFalse($schema->exists());
    }
}

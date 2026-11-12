<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Functional\Driver\MySQL;

// phpcs:ignore
use Arcano\Database\Driver\MySQL\Schema\MySQLTable;
use Arcano\Database\Tests\Functional\Driver\Common\BuildersAccessTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class BuildersAccessTest extends CommonClass
{
    public const DRIVER = 'mysql';

    public function testTableSchemaAccess(): void
    {
        parent::testTableSchemaAccess();
        $this->assertInstanceOf(
            MySQLTable::class,
            $this->db()->table('sample')->getSchema(),
        );
    }
}

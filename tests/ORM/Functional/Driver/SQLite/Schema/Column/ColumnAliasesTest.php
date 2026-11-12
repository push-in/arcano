<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLite\Schema\Column;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Schema\Column\ColumnAliasesTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class ColumnAliasesTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

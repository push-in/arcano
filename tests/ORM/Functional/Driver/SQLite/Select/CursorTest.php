<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLite\Select;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Select\CursorTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class CursorTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

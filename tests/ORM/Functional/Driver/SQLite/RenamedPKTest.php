<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLite;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\RenamedPKTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class RenamedPKTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

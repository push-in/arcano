<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLite\Integration\Issue356;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Issue356\CaseTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class CaseTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

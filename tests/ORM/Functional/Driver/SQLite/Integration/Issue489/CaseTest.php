<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLite\Integration\Issue489;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Issue489\CaseTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class CaseTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

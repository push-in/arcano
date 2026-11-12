<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLite\Integration\Case431;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Case431\CaseTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class CaseTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

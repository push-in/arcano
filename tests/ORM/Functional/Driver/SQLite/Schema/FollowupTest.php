<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLite\Schema;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Schema\FollowupTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class FollowupTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

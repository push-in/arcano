<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Integration\Issue422;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Issue422\CaseTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class CaseTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Integration\Issue322;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Issue322\CaseTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class CaseTest extends CommonClass
{
    public const DRIVER = 'mysql';
}

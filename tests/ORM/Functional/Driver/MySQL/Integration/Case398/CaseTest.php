<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\MySQL\Integration\Case398;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Case398\CaseTest as CommonClass;

/**
 * @group driver
 * @group driver-mysql
 */
class CaseTest extends CommonClass
{
    public const DRIVER = 'mysql';

    public function testSelectWithFullJoin(): void
    {
        $this->markTestSkipped('MySQL does not support FULL JOIN');
    }
}

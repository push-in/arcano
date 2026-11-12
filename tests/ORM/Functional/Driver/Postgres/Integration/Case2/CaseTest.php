<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Postgres\Integration\Case2;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Case2\CaseTest as CommonClass;

/**
 * @group driver
 * @group driver-postgres
 */
class CaseTest extends CommonClass
{
    public const DRIVER = 'postgres';
}

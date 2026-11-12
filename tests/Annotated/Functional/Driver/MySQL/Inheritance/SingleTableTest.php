<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\MySQL\Inheritance;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\Inheritance\SingleTableTestCase;

/**
 * @group driver
 * @group driver-mysql
 */
final class SingleTableTest extends SingleTableTestCase
{
    public const DRIVER = 'mysql';
}

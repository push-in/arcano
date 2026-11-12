<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\MySQL;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\TypecastTestCase;

/**
 * @group driver
 * @group driver-mysql
 */
final class TypecastTest extends TypecastTestCase
{
    public const DRIVER = 'mysql';
}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\MySQL;

// phpcs:ignore
use Arcano\Annotated\Tests\Functional\Driver\Common\InvalidTestCase;

/**
 * @group driver
 * @group driver-mysql
 */
final class InvalidTest extends InvalidTestCase
{
    public const DRIVER = 'mysql';
}

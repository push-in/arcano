<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLite\Typecast;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Typecast\JsonTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
final class JsonTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

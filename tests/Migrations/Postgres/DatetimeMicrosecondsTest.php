<?php

declare(strict_types=1);

namespace Arcano\Migrations\Tests\Postgres;

/**
 * @group driver
 * @group driver-postgres
 */
class DatetimeMicrosecondsTest extends \Arcano\Migrations\Tests\DatetimeMicrosecondsTest
{
    public const DRIVER = 'postgres';
}

<?php

declare(strict_types=1);

namespace Arcano\Database;

use Arcano\Database\Driver\DriverInterface;
use Psr\Log\LoggerInterface;

interface LoggerFactoryInterface
{
    public function getLogger(?DriverInterface $driver = null): LoggerInterface;
}

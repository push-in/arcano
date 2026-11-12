<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Integration\Case408\Entity;

use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Case408\Type\MonitorInterval;
use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Case408\Type\PublicHostname;

class PingMonitor extends Target
{
    private PublicHostname $hostname;
    private MonitorInterval $monitorInterval;

    public function __construct(string $hostname = '', int $monitorInterval = 5)
    {
        $this->hostname = PublicHostname::create($hostname);
        $this->monitorInterval = MonitorInterval::create($monitorInterval);
    }
}

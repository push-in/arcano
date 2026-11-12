<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Integration\Case408\Entity;

use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Case408\Type\MonitorName;
use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Case408\Type\TargetGroupId;
use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Case408\Type\TargetId;

class Target
{
    protected ?TargetId $id = null;
    private TargetGroupId $target_group_id;
    private MonitorName $monitorName;

    public function __construct(string $monitorName = '')
    {
        $this->monitorName = MonitorName::create($monitorName);
    }
}

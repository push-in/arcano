<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Inheritance\Fixture;

class BossWithKind extends WorkerWithKind
{
    public ?int $level = null;
}

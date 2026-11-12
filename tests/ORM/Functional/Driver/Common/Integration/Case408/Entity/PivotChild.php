<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Integration\Case408\Entity;

use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Case408\Type\Rate;

class PivotChild extends Pivot
{
    private Rate $rate;

    public function __construct(int $rate)
    {
        $this->rate = Rate::create($rate);
    }
}

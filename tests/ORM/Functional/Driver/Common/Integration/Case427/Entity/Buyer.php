<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Integration\Case427\Entity;

class Buyer
{
    public array $partners = [];

    public function __construct(
        public int $id,
        public string $address,
    ) {}
}

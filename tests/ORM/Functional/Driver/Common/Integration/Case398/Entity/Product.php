<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Integration\Case398\Entity;

class Product
{
    public const ROLE = 'product';

    public int $id;

    public function __construct(
        public string $title,
    ) {}
}

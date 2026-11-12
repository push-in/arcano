<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Integration\Issue422\Entity;

class Billing
{
    public ?int $id = null;
    public SomeEmbedded $someEmbedded;
}

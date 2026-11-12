<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Integration\Issue422\Entity;

class User
{
    public ?int $id = null;
    public ?Billing $billing = null;
    public SomeEmbedded $otherEmbedded;
}

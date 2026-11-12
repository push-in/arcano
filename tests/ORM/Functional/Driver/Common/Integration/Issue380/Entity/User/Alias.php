<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Integration\Issue380\Entity\User;

use Arcano\ORM\Tests\Functional\Driver\Common\Integration\Issue380\Entity\User;

class Alias
{
    public int $id;
    public string $value;
    public User $user;

    public function __construct(User $user, string $value)
    {
        $this->user = $user;
        $this->value = $value;
    }
}

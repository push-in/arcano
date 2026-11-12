<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Inheritance\Fixture;

use Arcano\ORM\Tests\Fixtures\UserCredentials;

class ManagerWithCredentials extends Employee
{
    public ?int $role_id = null;
    public ?int $level = null;
    public string $rank = 'none';
    public ?UserCredentials $credentials = null;
}

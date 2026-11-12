<?php

// phpcs:ignoreFile
declare(strict_types=1);

namespace Arcano\ORM\Tests\Fixtures;

use Arcano\ORM\Select\Repository;

class UserRepository extends Repository
{
    public function findByEmail(string $email): ?User
    {
        return $this->findOne(compact('email'));
    }
}

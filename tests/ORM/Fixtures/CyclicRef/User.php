<?php

// phpcs:ignoreFile
declare(strict_types=1);

namespace Arcano\ORM\Tests\Fixtures\CyclicRef;

class User
{
    public $id;
    public $email;
    public $created_at;
    public $updated_at;
}

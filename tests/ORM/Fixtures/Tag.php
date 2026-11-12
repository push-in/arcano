<?php

// phpcs:ignoreFile
declare(strict_types=1);

namespace Arcano\ORM\Tests\Fixtures;

use Arcano\ORM\Collection\Pivoted\PivotedCollection;

class Tag
{
    public $id;
    public $name;
    public $users;

    public function __construct()
    {
        $this->users = new PivotedCollection();
    }
}

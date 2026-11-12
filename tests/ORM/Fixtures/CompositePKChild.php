<?php

// phpcs:ignoreFile
declare(strict_types=1);

namespace Arcano\ORM\Tests\Fixtures;

use Arcano\ORM\Collection\Pivoted\PivotedCollection;

class CompositePKChild
{
    public $key1;
    public $key2;
    public $key3;
    public $key4;
    public $parent_key1;
    public $parent_key2;
    public $parent_key3;
    public $parent_key4;
    public $parent;
    public $nested;
    public $pivoted;

    public function __construct()
    {
        $this->pivoted = new PivotedCollection();
    }
}

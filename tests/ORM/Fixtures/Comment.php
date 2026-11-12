<?php

// phpcs:ignoreFile
declare(strict_types=1);

namespace Arcano\ORM\Tests\Fixtures;

use Arcano\ORM\Collection\Pivoted\PivotedCollection;
use Arcano\ORM\Collection\Pivoted\PivotedCollectionInterface;

class Comment
{
    public $id;
    public $message;

    /** @var User */
    public $user;

    /** @var PivotedCollectionInterface|User[] */
    public $favoredBy;

    public $parent;
    public $level;
    public UserWithUUIDPrimaryKey $userWithUuid;

    public function __construct()
    {
        $this->favoredBy = new PivotedCollection();
    }
}

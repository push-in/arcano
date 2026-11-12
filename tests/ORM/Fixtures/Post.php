<?php

// phpcs:ignoreFile
declare(strict_types=1);

namespace Arcano\ORM\Tests\Fixtures;

use Arcano\ORM\Collection\Pivoted\PivotedCollectionInterface;

class Post implements ImagedInterface
{
    public $id;
    public $user;
    public $title;
    public $content;
    public $image;
    public $lock;

    /** @var Comment[]|PivotedCollectionInterface */
    public $comments;

    public ?Post $parent = null;
    public UserWithUUIDPrimaryKey $userWithUuidPK;
}

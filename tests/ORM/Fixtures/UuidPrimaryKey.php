<?php

// phpcs:ignoreFile
declare(strict_types=1);

namespace Arcano\ORM\Tests\Fixtures;

use Arcano\Database\DatabaseInterface;

class UuidPrimaryKey
{
    private $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public static function typecast($value, DatabaseInterface $database)
    {
        return new self($value);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->id;
    }
}

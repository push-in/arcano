<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Fixtures;

final class JsonSerializableClass implements \JsonSerializable
{
    public function jsonSerialize(): array
    {
        return [
            'foo' => 'Lorem',
            'bar' => 'Ipsum',
        ];
    }
}

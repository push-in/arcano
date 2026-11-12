<?php

declare(strict_types=1);

namespace Arcano\ORM\Parser;

use Arcano\ORM\Exception\TypecastException;

interface CastableInterface extends TypecastInterface
{
    /**
     * Typecast key-values into an internal representation.
     *
     * @throws TypecastException
     */
    public function cast(array $data): array;
}

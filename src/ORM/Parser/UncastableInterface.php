<?php

declare(strict_types=1);

namespace Arcano\ORM\Parser;

use Arcano\ORM\Exception\TypecastException;

interface UncastableInterface extends TypecastInterface
{
    /**
     * Uncast key-values into a database representation.
     *
     * @throws TypecastException
     */
    public function uncast(array $data): array;
}

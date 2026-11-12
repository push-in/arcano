<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures1\Typecast;

use Arcano\ORM\Parser\TypecastInterface;

class UuidTypecaster implements TypecastInterface
{
    public function setRules(array $rules): array
    {
        return $rules;
    }

    public function cast(array $values): array
    {
        return $values;
    }
}

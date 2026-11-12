<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Fixtures;

use Arcano\ORM\Parser\TypecastInterface;

class Typecaster implements TypecastInterface
{
    public function cast(array $values): array {}

    public function setRules(array $rules): array {}
}

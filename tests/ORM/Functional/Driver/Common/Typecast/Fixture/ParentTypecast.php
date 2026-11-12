<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Typecast\Fixture;

use Arcano\ORM\Parser\TypecastInterface;

class ParentTypecast implements TypecastInterface
{
    private bool $set = false;

    public function setRules(array $rules): array
    {
        if ($this->set) {
            throw new \Exception('ParentTypecast should be run only once');
        }

        $this->set = true;

        return $rules;
    }

    public function cast(array $values): array
    {
        return $values;
    }
}

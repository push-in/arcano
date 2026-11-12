<?php

declare(strict_types=1);

namespace Arcano\Annotated\Annotation\Relation\Traits;

use Arcano\Annotated\Annotation\Relation\Inverse;

trait InverseTrait
{
    protected ?Inverse $inverse = null;

    public function getInverse(): ?Inverse
    {
        return $this->inverse;
    }
}

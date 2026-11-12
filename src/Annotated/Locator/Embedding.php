<?php

declare(strict_types=1);

namespace Arcano\Annotated\Locator;

use Arcano\Annotated\Annotation\Embeddable;

final class Embedding
{
    public function __construct(
        public readonly Embeddable $attribute,
        public readonly \ReflectionClass $class,
    ) {}
}

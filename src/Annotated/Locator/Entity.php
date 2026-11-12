<?php

declare(strict_types=1);

namespace Arcano\Annotated\Locator;

use Arcano\Annotated\Annotation\Entity as Attribute;

final class Entity
{
    public function __construct(
        public readonly Attribute $attribute,
        public readonly \ReflectionClass $class,
    ) {}
}

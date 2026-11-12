<?php

declare(strict_types=1);

namespace Arcano\Annotated\Locator;

interface EntityLocatorInterface
{
    /**
     * @return Entity[]
     */
    public function getEntities(): array;
}

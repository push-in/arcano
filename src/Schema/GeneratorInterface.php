<?php

declare(strict_types=1);

namespace Arcano\Schema;

interface GeneratorInterface
{
    /**
     * Run generator over given registry.
     *
     */
    public function run(Registry $registry): Registry;
}

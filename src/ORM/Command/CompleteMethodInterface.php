<?php

declare(strict_types=1);

namespace Arcano\ORM\Command;

interface CompleteMethodInterface extends CommandInterface
{
    /**
     * Complete command, method to be called when all other commands are already executed and
     * transaction is closed.
     */
    public function complete(): void;
}

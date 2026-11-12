<?php

declare(strict_types=1);

namespace Arcano\ORM\Command;

interface RollbackMethodInterface extends CommandInterface
{
    /**
     * Rollback command or declare that command been rolled back.
     */
    public function rollBack(): void;
}

<?php

declare(strict_types=1);

namespace Arcano\ORM\Transaction;

use Arcano\ORM\Command\CommandInterface;
use Arcano\ORM\ORMInterface;

interface CommandGeneratorInterface
{
    public function generateStoreCommand(ORMInterface $orm, Tuple $tuple): ?CommandInterface;

    public function generateDeleteCommand(ORMInterface $orm, Tuple $tuple): ?CommandInterface;
}

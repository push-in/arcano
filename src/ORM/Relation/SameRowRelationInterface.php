<?php

declare(strict_types=1);

namespace Arcano\ORM\Relation;

use Arcano\ORM\Command\StoreCommandInterface;
use Arcano\ORM\Transaction\Pool;
use Arcano\ORM\Transaction\Tuple;

/**
 * @internal
 */
interface SameRowRelationInterface extends ActiveRelationInterface
{
    public function queue(Pool $pool, Tuple $tuple, ?StoreCommandInterface $command = null): void;
}

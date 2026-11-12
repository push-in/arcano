<?php

// phpcs:ignoreFile
declare(strict_types=1);

namespace Arcano\ORM\Tests\Fixtures;

use Arcano\ORM\ORMInterface;
use Arcano\ORM\Select;
use Arcano\ORM\Select\Repository;
use Arcano\ORM\Transaction;

class UserPersistRepository extends Repository
{
    /** @var Transaction */
    private $transaction;

    public function __construct(Select $select, ORMInterface $orm)
    {
        parent::__construct($select);
        $this->transaction = new Transaction($orm);
    }

    /**
     *
     * @throws \Throwable
     */
    public function save(User $user, bool $cascade = true): void
    {
        $this->transaction->persist(
            $user,
            $cascade ? Transaction::MODE_CASCADE : Transaction::MODE_ENTITY_ONLY,
        );

        $this->transaction->run(); // transaction is clean after run
    }
}

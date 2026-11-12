<?php

declare(strict_types=1);

namespace Arcano\ORM\Command\Database;

use Arcano\ORM\Command\DatabaseCommand;
use Arcano\ORM\Command\ScopeCarrierInterface;
use Arcano\ORM\Command\Traits\ErrorTrait;
use Arcano\ORM\Command\Traits\MapperTrait;
use Arcano\ORM\Command\Traits\ScopeTrait;
use Arcano\ORM\Exception\CommandException;
use Arcano\ORM\Heap\Node;
use Arcano\ORM\Heap\State;
use Arcano\Database\DatabaseInterface;
use Arcano\ORM\MapperInterface;

final class Delete extends DatabaseCommand implements ScopeCarrierInterface
{
    use ErrorTrait;
    use MapperTrait;
    use ScopeTrait;

    public function __construct(
        DatabaseInterface $db,
        string $table,
        private State $state,
        ?MapperInterface $mapper,
    ) {
        parent::__construct($db, $table);
        $this->mapper = $mapper;
    }

    public function isReady(): bool
    {
        return $this->isScopeReady();
    }

    /**
     * Inserting data into associated table.
     */
    public function execute(): void
    {
        if ($this->scope === []) {
            throw new CommandException('Unable to execute delete command without a scope.');
        }

        $scope = $this->scope;

        $this->affectedRows = $this->db->delete(
            $this->table,
            $this->prepareData($scope),
        )->run();
        $this->state->setStatus(Node::DELETED);

        parent::execute();
    }
}

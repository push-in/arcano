<?php

declare(strict_types=1);

namespace Arcano\ORM\Service\Implementation;

use Arcano\ORM\Exception\ORMException;
use Arcano\ORM\FactoryInterface;
use Arcano\ORM\ORMInterface;
use Arcano\ORM\Service\RepositoryProviderInterface;
use Arcano\ORM\Service\SourceProviderInterface;
use Arcano\ORM\RepositoryInterface;
use Arcano\ORM\SchemaInterface;
use Arcano\ORM\Select;

/**
 * @internal
 */
final class RepositoryProvider implements RepositoryProviderInterface
{
    /** @var array<non-empty-string, RepositoryInterface<object>> */
    private array $repositories = [];

    public function __construct(
        private ?ORMInterface $orm,
        private SourceProviderInterface $sourceProvider,
        private SchemaInterface $schema,
        private FactoryInterface $factory,
    ) {}

    public function getRepository(string $entity): RepositoryInterface
    {
        if (isset($this->repositories[$entity])) {
            return $this->repositories[$entity];
        }
        if ($this->orm === null) {
            throw new ORMException('Repository is not prepared.');
        }

        $select = null;

        if ($this->schema->define($entity, SchemaInterface::TABLE) !== null) {
            $select = new Select($this->orm, $entity);
            $select->scope($this->sourceProvider->getSource($entity)->getScope());
        }

        return $this->repositories[$entity] = $this->factory->repository($this->orm, $this->schema, $entity, $select);
    }

    public function prepareRepositories(): void
    {
        if ($this->orm === null) {
            return;
        }
        foreach ($this->schema->getRoles() as $role) {
            $this->getRepository($role);
        }
        $this->orm = null;
        unset($this->sourceProvider, $this->schema, $this->factory);
    }
}

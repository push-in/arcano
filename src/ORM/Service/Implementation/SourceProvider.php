<?php

declare(strict_types=1);

namespace Arcano\ORM\Service\Implementation;

use Arcano\ORM\FactoryInterface;
use Arcano\ORM\Service\SourceProviderInterface;
use Arcano\ORM\SchemaInterface;
use Arcano\ORM\Select\SourceInterface;

/**
 * @internal
 */
final class SourceProvider implements SourceProviderInterface
{
    /** @var SourceInterface[] */
    private array $sources = [];

    public function __construct(
        private FactoryInterface $factory,
        private SchemaInterface $schema,
    ) {}

    /**
     * @param non-empty-string $entity
     */
    public function getSource(string $entity): SourceInterface
    {
        return $this->sources[$entity] ?? ($this->sources[$entity] = $this->factory->source($this->schema, $entity));
    }
}

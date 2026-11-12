<?php

declare(strict_types=1);

namespace Arcano\ORM\Service\Implementation;

use Arcano\ORM\FactoryInterface;
use Arcano\ORM\Parser\TypecastInterface;
use Arcano\ORM\Service\SourceProviderInterface;
use Arcano\ORM\Service\TypecastProviderInterface;
use Arcano\ORM\SchemaInterface;

/**
 * @internal
 */
final class TypecastProvider implements TypecastProviderInterface
{
    /** @var array<non-empty-string, TypecastInterface|null> */
    private array $typecasts = [];

    public function __construct(
        private FactoryInterface $factory,
        private SchemaInterface $schema,
        private SourceProviderInterface $sourceProvider,
    ) {}

    public function getTypecast(string $role): ?TypecastInterface
    {
        return \array_key_exists($role, $this->typecasts)
            ? $this->typecasts[$role]
            : ($this->typecasts[$role] = $this->factory->typecast(
                $this->schema,
                $this->sourceProvider->getSource($role)->getDatabase(),
                $role,
            ));
    }
}

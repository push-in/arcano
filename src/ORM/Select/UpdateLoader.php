<?php

declare(strict_types=1);

namespace Arcano\ORM\Select;

use Arcano\ORM\FactoryInterface;
use Arcano\ORM\Parser\AbstractNode;
use Arcano\ORM\Parser\StaticNode;
use Arcano\ORM\Service\SourceProviderInterface;
use Arcano\ORM\SchemaInterface;
use Arcano\ORM\Select\Traits\ColumnsTrait;
use Arcano\ORM\Select\Traits\ScopeTrait;

/**
 * @internal
 */
final class UpdateLoader extends AbstractLoader
{
    use ColumnsTrait;
    use ScopeTrait;

    protected array $options = [
        'load' => true,
        'scope' => true,
    ];

    /**
     * @param non-empty-string $target Entity role or alias.
     */
    public function __construct(
        SchemaInterface $ormSchema,
        SourceProviderInterface $sourceProvider,
        FactoryInterface $factory,
        string $target,
    ) {
        parent::__construct($ormSchema, $sourceProvider, $factory, $target);
        $this->columns = $this->normalizeColumns($this->define(SchemaInterface::COLUMNS));
    }

    public function getAlias(): string
    {
        return $this->target;
    }

    public function loadData(AbstractNode $node, bool $includeRole = false): void
    {
        // loading child datasets
        foreach ($this->load as $relation => $loader) {
            $loader->loadData($node->getNode($relation), $includeRole);
        }

        // $this->loadHierarchy($node, $includeRole);
    }

    public function isLoaded(): bool
    {
        return true;
    }

    protected function initNode(): StaticNode
    {
        return new StaticNode($this->columnNames(), (array) $this->define(SchemaInterface::PRIMARY_KEY));
    }
}

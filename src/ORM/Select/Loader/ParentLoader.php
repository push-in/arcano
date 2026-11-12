<?php

declare(strict_types=1);

namespace Arcano\ORM\Select\Loader;

use Arcano\Database\Query\SelectQuery;
use Arcano\ORM\FactoryInterface;
use Arcano\ORM\Parser\AbstractNode;
use Arcano\ORM\Parser\ParentMergeNode;
use Arcano\ORM\Service\SourceProviderInterface;
use Arcano\ORM\Relation;
use Arcano\ORM\SchemaInterface;
use Arcano\ORM\Select\JoinableLoader;
use Arcano\ORM\Select\Traits\JoinOneTableTrait;

/**
 * Load parent data.
 *
 * @internal
 */
class ParentLoader extends JoinableLoader
{
    use JoinOneTableTrait;

    /**
     * Default set of relation options. Child implementation might defined their of default options.
     */
    protected array $options = [
        'load' => true,
        'constrain' => true,
        'method' => self::INLOAD,
        'minify' => true,
        'as' => null,
        'using' => null,
    ];

    public function __construct(
        SchemaInterface $ormSchema,
        SourceProviderInterface $sourceProvider,
        FactoryInterface $factory,
        string $role,
        string $target,
    ) {
        $schemaArray = [
            Relation::INNER_KEY => $ormSchema->define($role, SchemaInterface::PRIMARY_KEY),
            Relation::OUTER_KEY => $ormSchema->define($role, SchemaInterface::PARENT_KEY)
                ?? $ormSchema->define($target, SchemaInterface::PRIMARY_KEY),
        ];
        $this->options['as'] ??= $target;
        parent::__construct($ormSchema, $sourceProvider, $factory, $role, $target, $schemaArray);
    }

    public function configureQuery(SelectQuery $query, array $outerKeys = []): SelectQuery
    {
        if ($this->options['using'] !== null) {
            // use pre-defined query
            return parent::configureQuery($query, $outerKeys);
        }

        $this->configureParentQuery($query, $outerKeys);

        return parent::configureQuery($query);
    }

    protected function generateSubclassLoaders(): iterable
    {
        return [];
    }

    protected function getJoinMethod(): string
    {
        return 'INNER';
    }

    protected function initNode(): AbstractNode
    {
        return new ParentMergeNode(
            $this->target,
            $this->columnNames(),
            (array) $this->define(SchemaInterface::PRIMARY_KEY),
            (array) $this->schema[Relation::OUTER_KEY],
            (array) $this->schema[Relation::INNER_KEY],
        );
    }
}

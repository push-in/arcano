<?php

declare(strict_types=1);

namespace Arcano\ORM\Parser;

/**
 * Node without specified parent.
 *
 * @internal
 */
final class RootNode extends OutputNode
{
    /**
     * @param string[] $columns
     * @param string[] $primaryKeys
     */
    public function __construct(array $columns, array $primaryKeys)
    {
        parent::__construct($columns, null);
        $this->setDuplicateCriteria($primaryKeys);
    }
}

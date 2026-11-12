<?php

declare(strict_types=1);

namespace Arcano\ORM\Parser;

/**
 * Provides ability to fetch node context.
 *
 * @internal
 */
abstract class OutputNode extends AbstractNode
{
    /**
     * Array used to aggregate all nested node results in a form of tree.
     */
    protected array $result = [];

    /**
     * Get resulted data tree.
     */
    public function getResult(): array
    {
        return $this->result;
    }

    protected function push(array &$data): void
    {
        $this->result[] = &$data;
    }
}

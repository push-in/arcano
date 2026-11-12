<?php

declare(strict_types=1);

namespace Arcano\Schema;

use Arcano\Schema\Exception\RelationException;
use Arcano\Schema\Relation\OptionSchema;

/**
 * Carries information about particular relation and table declaration required to properly
 * map two or more entities.
 */
interface RelationInterface extends SchemaModifierInterface
{
    /**
     * Create relation version linked to specific entity context.
     *
     * @param non-empty-string $source
     * @param non-empty-string $target
     *
     * @throws RelationException
     */
    public function withContext(
        string $name,
        string $source,
        string $target,
        OptionSchema $options,
    ): self;
}

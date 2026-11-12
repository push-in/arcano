<?php

declare(strict_types=1);

namespace Arcano\ORM\Relation;

/**
 * Identical to RelationInterface but defines "left" side of the graph (relation to parent objects).
 *
 * @internal
 */
interface ReversedRelationInterface extends RelationInterface {}

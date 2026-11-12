<?php

declare(strict_types=1);

namespace Arcano\ORM\Relation\Morphed;

use Arcano\ORM\Exception\RelationException;
use Arcano\ORM\Heap\Node;
use Arcano\ORM\ORMInterface;
use Arcano\ORM\Reference\EmptyReference;
use Arcano\ORM\Reference\Reference;
use Arcano\ORM\Reference\ReferenceInterface;
use Arcano\ORM\Relation;
use Arcano\ORM\Relation\BelongsTo;
use Arcano\ORM\Transaction\Pool;
use Arcano\ORM\Transaction\Tuple;

/**
 * @internal
 */
class BelongsToMorphed extends BelongsTo
{
    private string $morphKey;

    public function __construct(ORMInterface $orm, string $role, string $name, string $target, array $schema)
    {
        parent::__construct($orm, $role, $name, $target, $schema);
        $this->morphKey = $schema[Relation::MORPH_KEY];
    }

    public function initReference(Node $node): ReferenceInterface
    {
        $scope = $this->getReferenceScope($node);
        $nodeData = $node->getData();
        if (!isset($nodeData[$this->morphKey], $scope)) {
            return new EmptyReference('?', null);
        }
        // $scope[$this->morphKey] = $nodeData[$this->morphKey];
        $target = $nodeData[$this->morphKey];

        return $scope === [] ? new EmptyReference($target, null) : new Reference($target, $scope);
    }

    public function prepare(Pool $pool, Tuple $tuple, mixed $related, bool $load = true): void
    {
        parent::prepare($pool, $tuple, $related, $load);
        $related = $tuple->state->getRelation($this->getName());

        if ($related === null || $related instanceof EmptyReference) {
            return;
        }

        $role = $related instanceof ReferenceInterface
            ? $related->getRole()
            : $pool->offsetGet($related)?->node->getRole();
        $tuple->state->register($this->morphKey, $role);
    }

    /**
     * Assert that given entity is allowed for the relation.
     *
     * @throws RelationException
     */
    protected function assertValid(Node $related): void
    {
        // no need to validate morphed relation yet
    }

    protected function setNullFromRelated(Tuple $tuple, bool $isPreparing): void
    {
        if ($tuple->node->getRelation($this->getName()) !== null) {
            // Set morph key to null if the relation was changed to null
            $tuple->state->register($this->morphKey, null);
        }

        parent::setNullFromRelated($tuple, $isPreparing);
    }
}

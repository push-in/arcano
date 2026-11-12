<?php

declare(strict_types=1);

namespace Arcano\ORM\Heap\Traits;

use Arcano\ORM\RelationMap;

trait RelationTrait
{
    private array $relations = [];

    /**
     * Relations whose references are created on first use (see {@see deferRelations()}).
     */
    private ?RelationMap $deferredRelations = null;

    /**
     * The references created for deferred relations, as of loading: the
     * entity's view of an unloaded relation, unlike {@see $relations} which
     * the unit of work updates.
     */
    private array $deferredReferences = [];

    /**
     * Postpones creating the references of relations that were not loaded
     * until one of them is read: most loaded entities never touch them.
     *
     * @internal
     */
    public function deferRelations(RelationMap $relationMap): void
    {
        $this->deferredRelations = $relationMap;
    }

    public function setRelation(string $name, mixed $context): void
    {
        $this->deferredRelations === null or $this->initDeferredRelations();
        $this->relations[$name] = $context;
        unset($this->data[$name]);
    }

    public function hasRelation(string $name): bool
    {
        $this->deferredRelations === null or $this->initDeferredRelations();
        return \array_key_exists($name, $this->relations);
    }

    public function getRelation(string $name): mixed
    {
        $this->deferredRelations === null or $this->initDeferredRelations();
        return $this->relations[$name] ?? null;
    }

    public function getRelations(): array
    {
        $this->deferredRelations === null or $this->initDeferredRelations();
        return $this->relations;
    }

    /**
     * The reference created on demand for an unloaded relation.
     *
     * @internal
     */
    public function getDeferredReference(string $name): mixed
    {
        $this->deferredRelations === null or $this->initDeferredRelations();
        return $this->deferredReferences[$name] ?? null;
    }

    /**
     * @internal
     */
    public function setDeferredReference(string $name, mixed $reference): void
    {
        $this->deferredReferences[$name] = $reference;
    }

    private function initDeferredRelations(): void
    {
        $relationMap = $this->deferredRelations;
        $this->deferredRelations = null;
        $relationMap->initReferences($this);
    }
}

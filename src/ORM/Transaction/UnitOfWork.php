<?php

declare(strict_types=1);

namespace Arcano\ORM\Transaction;

use Arcano\ORM\Command\CommandInterface;
use Arcano\ORM\Command\Database\StatementPlans;
use Arcano\ORM\EntityProxyInterface;
use Arcano\ORM\Mapper\Mapper;
use Arcano\ORM\SchemaInterface;
use Arcano\ORM\Command\Special\Sequence;
use Arcano\ORM\Exception\PoolException;
use Arcano\ORM\Exception\SuccessTransactionRetryException;
use Arcano\ORM\Exception\TransactionException;
use Arcano\Database\DatabaseInterface;
use Arcano\ORM\Heap\Heap;
use Arcano\ORM\Heap\Node;
use Arcano\ORM\Reference\ReferenceInterface;
use Arcano\ORM\Options;
use Arcano\ORM\ORMInterface;
use Arcano\ORM\Relation\SpecialValue;
use Arcano\ORM\Service\IndexProviderInterface;
use Arcano\ORM\Service\RelationProviderInterface;
use Arcano\ORM\Relation\RelationInterface;
use Arcano\ORM\Relation\ShadowBelongsTo;
use Arcano\ORM\RelationMap;

final class UnitOfWork implements StateInterface
{
    private const RELATIONS_NOT_RESOLVED = 0;
    private const RELATIONS_RESOLVED = 1;
    private const RELATIONS_DEFERRED = 2;
    private const STAGE_PREPARING = 0;
    private const STAGE_PROCESS = 1;
    private const STAGE_FINISHED = 2;

    private int $stage = self::STAGE_PREPARING;
    private Pool $pool;
    private CommandGeneratorInterface $commandGenerator;
    private ?\Throwable $error = null;
    private bool $ignoreUninitializedRelations;

    /**
     * Managed entities stored while nothing else was scheduled. When none of them has
     * touched relations, {@see runDirect()} updates them without the pool.
     *
     * @var array<int, array{object, bool}>
     */
    private array $direct = [];
    private bool $directOpen = true;
    private ?DirectInsert $directInsert = null;

    public function __construct(
        private ORMInterface $orm,
        private ?RunnerInterface $runner = null,
    ) {
        $this->pool = new Pool($orm);
        $this->commandGenerator = $orm->getCommandGenerator();
        $this->ignoreUninitializedRelations = $orm->getService(Options::class)->ignoreUninitializedRelations;
    }

    public function isSuccess(): bool
    {
        return $this->stage === self::STAGE_FINISHED;
    }

    public function getLastError(): ?\Throwable
    {
        return $this->error;
    }

    public function retry(): static
    {
        return $this->run();
    }

    public function persistState(object $entity, bool $cascade = true): self
    {
        $this->checkActionPossibility('persist entity');
        $this->flushDirect();
        $this->pool->attachStore($entity, $cascade, persist: true);

        return $this;
    }

    public function persistDeferred(object $entity, bool $cascade = true): self
    {
        $this->checkActionPossibility('schedule entity storing');
        if ($this->directOpen && (
            $entity instanceof EntityProxyInterface
                ? \property_exists($entity, '__arcano_node')
                : !$entity instanceof ReferenceInterface
        )) {
            $this->direct[\spl_object_id($entity)] = [$entity, $cascade];
            return $this;
        }
        $this->flushDirect();
        $this->pool->attachStore($entity, $cascade);

        return $this;
    }

    public function delete(object $entity, bool $cascade = true): self
    {
        $this->checkActionPossibility('schedule entity deletion');
        $this->flushDirect();
        $this->pool->attach($entity, Tuple::TASK_FORCE_DELETE, $cascade);

        return $this;
    }

    public function run(): StateInterface
    {
        $this->stage = match ($this->stage) {
            self::STAGE_FINISHED => throw new SuccessTransactionRetryException(
                'A successful transaction cannot be re-run.',
            ),
            self::STAGE_PROCESS => throw new TransactionException('Can\'t run started transaction.'),
            default => self::STAGE_PROCESS,
        };

        if ($this->direct !== []) {
            $plan = $this->runner === null ? $this->directPlan() : null;
            if ($plan !== null) {
                return $this->runDirect($plan);
            }
            $this->flushDirect();
        }

        $this->runner ??= Runner::innerTransaction();

        try {
            try {
                $this->walkPool();
            } catch (PoolException $e) {
                // Generate detailed exception about unresolved relations
                throw TransactionException::unresolvedRelations(
                    $this->pool->getUnresolved(),
                    $this->orm->getService(RelationProviderInterface::class),
                    $e,
                );
            }
        } catch (\Throwable $e) {
            $this->runner->rollback();
            $this->pool->closeIterator();

            // no calculations must be kept in node states, resetting
            // this will keep entity data as it was before transaction run
            $this->resetHeap();

            $this->error = $e;
            $this->stage = self::STAGE_PREPARING;

            return $this;
        }

        // we are ready to commit all changes to our representation layer
        $this->syncHeap();

        $this->runner->complete();
        $this->error = null;
        $this->stage = self::STAGE_FINISHED;
        // Clear state
        unset($this->orm, $this->runner, $this->pool, $this->commandGenerator);

        return $this;
    }

    public function setRunner(RunnerInterface $runner): void
    {
        $this->runner = $runner;
    }

    /**
     * Check if there are pending changes in the unit of work.
     *
     * @return bool True if there are pending changes, false otherwise.
     *         In case the transaction is finished, it will always return false.
     *         In case the transaction is in process, it will always return true.
     *         In case of failure, it will return true and pending changes can be retried.
     */
    public function hasPendingChanges(): bool
    {
        return isset($this->pool) && ($this->direct !== [] || $this->pool->count() > 0);
    }

    /**
     * @throws TransactionException
     */
    private function checkActionPossibility(string $action): void
    {
        $this->stage === self::STAGE_PROCESS && throw new TransactionException("Can't $action. Transaction began.");
        $this->stage === self::STAGE_FINISHED && throw new TransactionException("Can't $action. Transaction finished.");
    }

    private function flushDirect(): void
    {
        $this->directOpen = false;
        foreach ($this->direct as [$entity, $cascade]) {
            $this->pool->attachStore($entity, $cascade);
        }
        $this->direct = [];
    }

    /**
     * Field changes of the directly stored entities, or null when any of them needs the pool.
     *
     * @return list<array{object, Node, Mapper, array, mixed, array, \Arcano\ORM\Select\SourceInterface, string}>|null
     */
    private function directPlan(): ?array
    {
        if ($this->commandGenerator::class !== CommandGenerator::class) {
            return null;
        }
        $heap = $this->orm->getHeap();
        $roles = [];
        $plan = [];
        $claimed = [];
        foreach ($this->direct as [$entity, $cascade]) {
            if (!$entity instanceof EntityProxyInterface) {
                // A new entity graph.
                if (!$heap instanceof Heap) {
                    return null;
                }
                $this->directInsert ??= new DirectInsert($this->orm, $heap);
                $op = $this->directInsert->plan($entity, $cascade, $this->direct, $claimed);
                if ($op === null) {
                    return null;
                }
                $plan[] = [true, $op];
                continue;
            }
            $node = $entity->__arcano_node;
            if (
                $node === null
                || $entity->__arcano_read !== []
                || $entity->__cycle_orm_rel_data !== []
                || $node->hasState()
                || $node->getStatus() !== Node::MANAGED
                || $heap->get($entity) !== $node
                || !$node->relationsUntouched()
            ) {
                return null;
            }
            $role = $node->getRole();
            $describe = $roles[$role] ??= $this->describeDirect($role);
            if ($describe === null) {
                return null;
            }
            [$mapper, $pk, $indexes, $source] = $describe;

            // Same comparison as State::getChanges() over the node data.
            $before = $node->getData();
            $raw = $node->getRawData();
            $changes = [];
            foreach ($mapper->fetchFields($entity) as $field => $value) {
                if (!\array_key_exists($field, $before)) {
                    $changes[$field] = $value;
                    continue;
                }
                $old = \array_key_exists($field, $raw) ? $raw[$field] : $before[$field];
                if ($value !== $old && Node::compare($value, $old) !== 0) {
                    $changes[$field] = $value;
                }
            }
            if ($changes === []) {
                continue;
            }
            if (\array_key_exists($pk, $changes) || !isset($before[$pk])) {
                return null;
            }
            $plan[] = [false, [$entity, $node, $mapper, $changes, $before[$pk], $indexes, $source, $pk]];
        }

        return $plan;
    }

    /**
     * @return array{Mapper, string, array, \Arcano\ORM\Select\SourceInterface}|null
     */
    private function describeDirect(string $role): ?array
    {
        $schema = $this->orm->getSchema();
        $mapper = $this->orm->getMapper($role);
        $pk = $schema->define($role, SchemaInterface::PRIMARY_KEY);
        $map = $this->orm->getRelationMap($role);
        if (
            $mapper::class !== Mapper::class
            || !\is_string($pk)
            || $schema->define($role, SchemaInterface::PARENT) !== null
            || ($schema->define($role, SchemaInterface::CHILDREN) ?? []) !== []
            || $map->hasDependencies()
            || $map->hasEmbedded()
        ) {
            return null;
        }

        return [
            $mapper,
            $pk,
            $this->orm->getService(IndexProviderInterface::class)->getIndexes($role),
            $this->orm->getSource($role),
        ];
    }

    /**
     * Runs the planned updates in one transaction per driver, then syncs the nodes.
     */
    private function runDirect(array $plan): StateInterface
    {
        $drivers = [];
        $begin = static function (DatabaseInterface $db) use (&$drivers): void {
            $driver = $db->getDriver();
            if (!\in_array($driver, $drivers, true)) {
                $driver->beginTransaction();
                $drivers[] = $driver;
            }
        };
        $results = [];
        try {
            foreach ($plan as $i => [$insert, $op]) {
                if ($insert) {
                    $results[$i] = $this->directInsert->execute($op, $begin);
                    continue;
                }
                [, , $mapper, $changes, $id, , $source, $pk] = $op;
                $db = $source->getDatabase();
                $begin($db);
                $values = $mapper->uncast($changes);
                $values = $mapper->mapColumns($values);
                $where = $mapper->uncast([$pk => $id]);
                $where = $mapper->mapColumns($where);
                StatementPlans::update($db, $source->getTable(), $values, $where)
                    ?? $db->update($source->getTable(), $values, $where)->run();
            }
            foreach (\array_reverse($drivers) as $driver) {
                $driver->commitTransaction();
            }
        } catch (\Throwable $e) {
            foreach (\array_reverse($drivers) as $driver) {
                $driver->rollbackTransaction();
            }
            $this->error = $e;
            $this->stage = self::STAGE_PREPARING;

            return $this;
        }

        $heap = $this->orm->getHeap();
        foreach ($plan as $i => [$insert, $op]) {
            if ($insert) {
                $this->directInsert->sync($op, $results[$i]);
                continue;
            }
            [$entity, $node, , $changes, , $indexes] = $op;
            $before = $node->getData();
            $reindex = $this->indexChanged($indexes, $before, $changes + $before);
            $reindex and $heap->detach($entity);
            $node->mergeData($changes);
            $reindex and $heap->attach($entity, $node, $indexes);
        }

        $this->direct = [];
        $this->error = null;
        $this->stage = self::STAGE_FINISHED;
        unset($this->orm, $this->runner, $this->pool, $this->commandGenerator, $this->directInsert);

        return $this;
    }

    private function runCommand(?CommandInterface $command): void
    {
        if ($command === null) {
            return;
        }
        $this->runner->run($command);
        $this->pool->someHappens();
    }

    /**
     * Sync all entity states with generated changes.
     */
    private function syncHeap(): void
    {
        $heap = $this->orm->getHeap();
        $relationProvider = $this->orm->getService(RelationProviderInterface::class);
        $indexProvider = $this->orm->getService(IndexProviderInterface::class);
        $indexes = [];
        foreach ($this->pool->getAllTuples() as $e => $tuple) {
            $node = $tuple->node;

            // marked as being deleted and has no external claims (GC like approach)
            $status = $node->getStatus();
            if ($status === Node::DELETED || $status === Node::SCHEDULED_DELETE) {
                $heap->detach($e);
                continue;
            }
            $role = $node->getRole();

            // reindex the entity while it has old data, unless no indexed value changed
            $node->setState($tuple->state);
            $roleIndexes = $indexes[$role] ??= $indexProvider->getIndexes($role);
            if (!$heap->has($e) || $this->indexChanged($roleIndexes, $node->getData(), $tuple->state->getData())) {
                $heap->attach($e, $node, $roleIndexes);
            }

            if ($tuple->persist !== null) {
                $syncData = \array_udiff_assoc(
                    $tuple->state->getData(),
                    $tuple->persist->getData(),
                    [Node::class, 'compare'],
                );
            } else {
                $syncData = $node->syncState($relationProvider->getRelationMap($role), $tuple->state);
            }

            $tuple->mapper->hydrate($e, $syncData);
        }
    }

    private function indexChanged(array $indexes, array $before, array $after): bool
    {
        foreach ($indexes as $index) {
            foreach ((array) $index as $key) {
                $old = $before[$key] ?? null;
                $new = $after[$key] ?? null;
                if ($old !== $new && ($old === null || $new === null || (string) $old !== (string) $new)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Reset heap to it's initial state and remove all the changes.
     */
    private function resetHeap(): void
    {
        foreach ($this->pool->getAllTuples() as $tuple) {
            $tuple->node->resetState();
        }
    }

    /**
     * Return flattened list of commands required to store and delete associated entities.
     */
    private function walkPool(): void
    {
        /**
         * @var object $entity
         * @var Tuple $tuple
         */
        foreach ($this->pool->openIterator() as $tuple) {
            if ($tuple->task === Tuple::TASK_FORCE_DELETE && !$tuple->cascade) {
                // currently we rely on db to delete all nested records (or soft deletes)
                $command = $this->commandGenerator->generateDeleteCommand($this->orm, $tuple);
                $this->runCommand($command);
                $tuple->status = Tuple::STATUS_PROCESSED;
                continue;
            }

            // Walk relations
            $this->resolveRelations($tuple);
        }
    }

    private function resolveMasterRelations(Tuple $tuple, RelationMap $map): int
    {
        if (!$map->hasDependencies()) {
            return self::RELATIONS_RESOLVED;
        }

        $deferred = false;
        $resolved = true;
        foreach ($map->getMasters() as $name => $relation) {
            $statusAfter = $statusBefore = $tuple->state->getRelationStatus($relation->getName());
            if ($statusBefore === RelationInterface::STATUS_RESOLVED) {
                continue;
            }

            if ($relation instanceof ShadowBelongsTo) {
                // Check relation is connected
                // Connected -> $parentNode->getRelationStatus()
                // Disconnected -> WAIT if Tuple::STATUS_PREPARING
                $relation->queue($this->pool, $tuple);
                $statusAfter = $tuple->state->getRelationStatus($relation->getName());
            } else {
                if ($tuple->status === Tuple::STATUS_PREPARING) {
                    if ($statusAfter === RelationInterface::STATUS_PREPARE) {
                        $entityData ??= $tuple->mapper->fetchRelations($tuple->entity);
                        $relation->prepare(
                            $this->pool,
                            $tuple,
                            \array_key_exists($name, $entityData)
                                ? $entityData[$name]
                                : ($this->ignoreUninitializedRelations ? SpecialValue::notSet() : null),
                        );
                        $statusAfter = $tuple->state->getRelationStatus($relation->getName());
                    }
                } else {
                    $relation->queue($this->pool, $tuple);
                    $statusAfter = $tuple->state->getRelationStatus($relation->getName());
                }
            }

            $statusAfter > $statusBefore and $this->pool->someHappens();
            $resolved = $resolved && $statusAfter >= RelationInterface::STATUS_DEFERRED;
            $deferred = $deferred || $statusAfter === RelationInterface::STATUS_DEFERRED;
        }

        // $tuple->waitKeys = array_unique(array_merge(...$waitKeys));
        return ($deferred ? self::RELATIONS_DEFERRED : 0) | ($resolved ? self::RELATIONS_RESOLVED : 0);
    }

    private function resolveSlaveRelations(Tuple $tuple, RelationMap $map): int
    {
        if (!$map->hasSlaves()) {
            return self::RELATIONS_RESOLVED;
        }
        $changedFields = \array_keys($tuple->state->getChanges());

        // Attach children to pool
        $transactData = $tuple->state->getTransactionData();
        $deferred = false;
        $resolved = true;
        if ($tuple->status === Tuple::STATUS_PREPARING) {
            $relData = $tuple->mapper->fetchRelations($tuple->entity);
        }
        foreach ($map->getSlaves() as $name => $relation) {
            $statusBefore = $statusAfter = $tuple->state->getRelationStatus($relation->getName());
            if (!$relation->isCascade() || $statusAfter === RelationInterface::STATUS_RESOLVED) {
                continue;
            }

            $innerKeys = $relation->getInnerKeys();
            $isWaitingKeys = \array_intersect($innerKeys, $tuple->state->getWaitingFields(true)) !== [];
            $hasChangedKeys = \array_intersect($innerKeys, $changedFields) !== [];
            if ($statusAfter === RelationInterface::STATUS_PREPARE) {
                $relData ??= $tuple->mapper->fetchRelations($tuple->entity);
                $relation->prepare(
                    $this->pool,
                    $tuple,
                    \array_key_exists($name, $relData)
                        ? $relData[$name]
                        : ($this->ignoreUninitializedRelations ? SpecialValue::notSet() : null),
                    $isWaitingKeys || $hasChangedKeys,
                );
                $statusAfter = $tuple->state->getRelationStatus($relation->getName());
            }

            if ($statusAfter !== RelationInterface::STATUS_PREPARE
                && $statusAfter !== RelationInterface::STATUS_RESOLVED
                && !$isWaitingKeys
                && !$hasChangedKeys
                && \count(\array_intersect($innerKeys, \array_keys($transactData))) === \count($innerKeys)
            ) {
                // $child ??= $tuple->state->getRelation($name);
                $relation->queue($this->pool, $tuple);
                $statusAfter = $tuple->state->getRelationStatus($relation->getName());
            }

            $statusAfter > $statusBefore and $this->pool->someHappens();
            $resolved = $resolved && $statusAfter === RelationInterface::STATUS_RESOLVED;
            $deferred = $deferred || $statusAfter === RelationInterface::STATUS_DEFERRED;
        }

        return ($deferred ? self::RELATIONS_DEFERRED : 0) | ($resolved ? self::RELATIONS_RESOLVED : 0);
    }

    private function resolveSelfWithEmbedded(Tuple $tuple, RelationMap $map, bool $hasDeferredRelations): void
    {
        $hasChanges = $tuple->state->hasChanges();
        if (!$hasChanges && !$map->hasEmbedded()) {
            $tuple->status = $hasDeferredRelations
                ? \max(Tuple::STATUS_DEFERRED_RESOLVED, $tuple->status)
                : Tuple::STATUS_PROCESSED;

            return;
        }
        $command = $this->commandGenerator->generateStoreCommand($this->orm, $tuple);

        if (!$map->hasEmbedded()) {
            // Not embedded but has changes
            $this->runCommand($command);

            $tuple->status = $tuple->status <= Tuple::STATUS_PROPOSED_RESOLVED && $hasDeferredRelations
                ? Tuple::STATUS_DEFERRED_RESOLVED
                : Tuple::STATUS_PROCESSED;

            return;
        }

        $entityData = $tuple->mapper->extract($tuple->entity);
        $chEmb = false;
        foreach ($map->getEmbedded() as $name => $relation) {
            $relationStatus = $tuple->state->getRelationStatus($relation->getName());
            if ($relationStatus === RelationInterface::STATUS_RESOLVED) {
                continue;
            }

            $chEmb = true;
            $tuple->state->setRelation($name, $entityData[$name] ?? null);
            // We can use class MergeCommand here
            $relation->queue(
                $this->pool,
                $tuple,
                $command instanceof Sequence ? $command->getPrimaryCommand() : $command,
            );
        }

        // Run command if there are embedded changes or other changes
        $chEmb || $hasChanges and $this->runCommand($command);

        $tuple->status = $tuple->status === Tuple::STATUS_PREPROCESSED || !$hasDeferredRelations
            ? Tuple::STATUS_PROCESSED
            : \max(Tuple::STATUS_DEFERRED_RESOLVED, $tuple->status);
    }

    private function resolveRelations(Tuple $tuple): void
    {
        $map = $this->orm->getRelationMap($tuple->node->getRole());

        $result = $tuple->task === Tuple::TASK_STORE
            ? $this->resolveMasterRelations($tuple, $map)
            : $this->resolveSlaveRelations($tuple, $map);
        $isDependenciesResolved = (bool) ($result & self::RELATIONS_RESOLVED);
        $deferred = (bool) ($result & self::RELATIONS_DEFERRED);

        // If deferred relations found, mark self as deferred
        if ($deferred) {
            $tuple->status === Tuple::STATUS_PROPOSED_RESOLVED or $this->pool->someHappens();
            $tuple->status = $isDependenciesResolved ? Tuple::STATUS_DEFERRED_RESOLVED : Tuple::STATUS_DEFERRED;
        }

        if ($isDependenciesResolved) {
            if ($tuple->task === Tuple::TASK_STORE) {
                $this->resolveSelfWithEmbedded($tuple, $map, $deferred);
            } elseif ($tuple->status === Tuple::STATUS_PREPARING) {
                $tuple->status = Tuple::STATUS_WAITING;
            } else {
                $command = $this->commandGenerator->generateDeleteCommand($this->orm, $tuple);
                $this->runCommand($command);
                $tuple->status = Tuple::STATUS_PROCESSED;
            }
        }

        if ($tuple->cascade) {
            $tuple->task === Tuple::TASK_STORE
                ? $this->resolveSlaveRelations($tuple, $map)
                : $this->resolveMasterRelations($tuple, $map);
        }

        if (!$isDependenciesResolved && $tuple->status === Tuple::STATUS_PREPROCESSED) {
            $tuple->status = Tuple::STATUS_UNPROCESSED;
        }
    }
}

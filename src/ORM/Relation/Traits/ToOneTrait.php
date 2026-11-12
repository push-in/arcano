<?php

declare(strict_types=1);

namespace Arcano\ORM\Relation\Traits;

use Arcano\ORM\Heap\Node;
use Arcano\ORM\Reference\EmptyReference;
use Arcano\ORM\Reference\Reference;
use Arcano\ORM\Reference\ReferenceInterface;
use Arcano\ORM\Select\LoaderInterface;
use Arcano\ORM\Service\EntityFactoryInterface;
use Arcano\ORM\Service\EntityProviderInterface;

/**
 * @internal
 */
trait ToOneTrait
{
    protected EntityProviderInterface $entityProvider;

    public function init(EntityFactoryInterface $factory, Node $node, array $data): object
    {
        $item = $factory->make($this->target, $data, Node::MANAGED);
        $node->setRelation($this->getName(), $item);
        return $item;
    }

    public function cast(?array $data): ?array
    {
        $role = $data[LoaderInterface::ROLE_KEY] ?? $this->target;
        return $data === null
            ? null
            : ($this->mapperProvider->getMapper($role)?->cast($data) ?? $data);
    }

    public function initReference(Node $node): ReferenceInterface
    {
        $scope = $this->getReferenceScope($node);
        if ($scope === null) {
            $result = new Reference($this->target, []);
            $result->setValue(null);
            return $result;
        }
        return $scope === [] ? new EmptyReference($this->target, null) : new Reference($this->target, $scope);
    }

    /**
     * Resolve the reference to the object.
     */
    public function resolve(ReferenceInterface $reference, bool $load): object|iterable|null
    {
        if ($reference->hasValue()) {
            return $reference->getValue();
        }

        $result = $this->entityProvider->get($reference->getRole(), $reference->getScope(), $load);
        if ($load === true || $result !== null) {
            $reference->setValue($result);
        }
        return $result;
    }

    public function collect(mixed $data): ?object
    {
        return $data;
    }

    protected function getReferenceScope(Node $node): ?array
    {
        $scope = [];
        $nodeData = $node->getData();
        foreach ($this->innerKeys as $i => $key) {
            if (!\array_key_exists($key, $nodeData)) {
                return [];
            }
            if ($nodeData[$key] === null) {
                return null;
            }
            $scope[$this->outerKeys[$i]] = $nodeData[$key];
        }
        return $scope;
    }
}

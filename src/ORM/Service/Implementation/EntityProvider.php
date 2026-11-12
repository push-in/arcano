<?php

declare(strict_types=1);

namespace Arcano\ORM\Service\Implementation;

use Arcano\ORM\Heap\HeapInterface;
use Arcano\ORM\Service\EntityProviderInterface;
use Arcano\ORM\Service\RepositoryProviderInterface;

/**
 * @internal
 */
final class EntityProvider implements EntityProviderInterface
{
    public function __construct(
        private HeapInterface $heap,
        private RepositoryProviderInterface $repositoryProvider,
    ) {}

    public function get(string $role, array $scope, bool $load = true): ?object
    {
        $e = $this->heap->find($role, $scope);

        if ($e !== null) {
            return $e;
        }

        if (!$load) {
            return null;
        }

        return $this->repositoryProvider->getRepository($role)->findOne($scope);
    }
}

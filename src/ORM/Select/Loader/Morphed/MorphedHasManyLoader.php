<?php

declare(strict_types=1);

namespace Arcano\ORM\Select\Loader\Morphed;

use Arcano\ORM\Relation;
use Arcano\ORM\Select\Loader\HasManyLoader;
use Arcano\ORM\Select\Traits\WhereTrait;
use Arcano\Database\Query\SelectQuery;

/**
 * Creates an additional query constrain based on parent entity alias.
 */
class MorphedHasManyLoader extends HasManyLoader
{
    use WhereTrait;

    public function configureQuery(SelectQuery $query, array $outerKeys = []): SelectQuery
    {
        return $this->setWhere(
            parent::configureQuery($query, $outerKeys),
            $this->isJoined() ? 'onWhere' : 'where',
            [$this->localKey(Relation::MORPH_KEY) => $this->parent->getTarget()],
        );
    }
}

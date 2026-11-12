<?php

declare(strict_types=1);

namespace Arcano\Annotated\Annotation\Table;

use Doctrine\Common\Annotations\Annotation\Target;
use Spiral\Attributes\NamedArgumentConstructor;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target("ANNOTATION", "CLASS")
 */
#[\Attribute(\Attribute::TARGET_CLASS), NamedArgumentConstructor]
class PrimaryKey extends Index
{
    public function __construct(array $columns = [])
    {
        $unique = true;
        $name = 'PK';

        parent::__construct(
            columns: $columns,
            unique: $unique,
            name: $name,
        );
    }
}

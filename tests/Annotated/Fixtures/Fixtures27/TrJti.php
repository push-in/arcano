<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures27;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Inheritance\JoinedTable;

#[Entity]
#[JoinedTable]
class TrJti extends TrParent
{
    #[Column(type: 'int')]
    public int $extra;
}

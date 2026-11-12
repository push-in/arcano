<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures27;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Inheritance\DiscriminatorColumn;

#[Entity]
#[DiscriminatorColumn(name: 'type')]
class TrParent
{
    use TrRelTrait;

    #[Column(type: 'primary', name: 'id')]
    public int $id;

    #[Column(type: 'string')]
    public string $type;
}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures27;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Inheritance\DiscriminatorColumn;
use Arcano\Annotated\Annotation\Relation\HasOne;

#[Entity]
#[DiscriminatorColumn(name: 'type')]
class SoParent
{
    #[Column(type: 'primary', name: 'id')]
    public int $id;

    #[Column(type: 'string')]
    public string $type;

    #[Column(type: 'int', nullable: true)]
    public ?int $target_id = null;

    #[HasOne(target: SoTarget::class, innerKey: 'target_id', outerKey: 'id', nullable: true, fkCreate: false)]
    public ?SoTarget $target = null;
}

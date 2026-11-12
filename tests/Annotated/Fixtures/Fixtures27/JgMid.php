<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures27;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Inheritance\JoinedTable;
use Arcano\Annotated\Annotation\Relation\HasOne;

#[Entity]
#[JoinedTable]
class JgMid extends JgParent
{
    #[Column(type: 'int', nullable: true)]
    public ?int $target_id = null;

    #[HasOne(target: JgTarget::class, innerKey: 'target_id', outerKey: 'id', nullable: true, fkCreate: false)]
    public ?JgTarget $target = null;
}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures27;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Inheritance\SingleTable;
use Arcano\Annotated\Annotation\Relation\HasOne;

#[Entity]
#[SingleTable]
class StChild extends StParent
{
    #[Column(type: 'int', nullable: true)]
    public ?int $target_id = null;

    #[HasOne(target: StTarget::class, innerKey: 'target_id', outerKey: 'id', nullable: true, fkCreate: false)]
    public ?StTarget $target = null;
}

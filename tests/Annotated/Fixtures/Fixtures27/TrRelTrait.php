<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures27;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Relation\HasOne;

trait TrRelTrait
{
    #[Column(type: 'int', nullable: true)]
    public ?int $target_id = null;

    #[HasOne(target: TrTarget::class, innerKey: 'target_id', outerKey: 'id', nullable: true, fkCreate: false)]
    public ?TrTarget $target = null;
}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures27;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Inheritance\DiscriminatorColumn;
use Arcano\Annotated\Annotation\Relation\BelongsTo;

#[Entity]
#[DiscriminatorColumn(name: 'type')]
class BtParent
{
    #[Column(type: 'primary', name: 'id')]
    public int $id;

    #[Column(type: 'string')]
    public string $type;

    #[BelongsTo(target: BtTarget::class, nullable: true, fkCreate: false)]
    public ?BtTarget $target = null;
}

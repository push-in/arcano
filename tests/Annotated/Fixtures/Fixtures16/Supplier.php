<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures16;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Inheritance\JoinedTable as InheritanceJoinedTable;
use Arcano\Annotated\Annotation\Table\Index;

/**
 * @Entity
 * @InheritanceJoinedTable
 * @Index(columns={"index_id"}, unique=true)
 */
#[Entity]
#[InheritanceJoinedTable]
#[Index(columns: ['index_id'], unique: true)]
class Supplier extends Person
{
    /** @Column(type="integer") */
    #[Column(type: 'integer')]
    private int $custom_id;

    /** @Column(type="integer") */
    #[Column(type: 'integer')]
    private int $index_id;
}

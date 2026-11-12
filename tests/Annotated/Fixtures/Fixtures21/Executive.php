<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures21;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Inheritance\JoinedTable;

/**
 * @Entity
 * @JoinedTable
 */
#[Entity]
#[JoinedTable]
class Executive extends Employee
{
    /** @Column(type="integer") */
    #[Column(type: 'integer')]
    public int $bonus;
}

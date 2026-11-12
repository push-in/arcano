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
class Buyer extends Person
{
    /** @Column(type="string") */
    #[Column(type: 'string')]
    public string $foo;

    /** @Column(type="string") */
    #[Column(type: 'string')]
    public string $bar;
}

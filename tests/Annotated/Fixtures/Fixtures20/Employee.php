<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures20;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Inheritance\SingleTable as InheritanceSingleTable;

/**
 * @Entity
 * @InheritanceSingleTable
 */
#[Entity]
#[InheritanceSingleTable]
class Employee extends Person
{
    /** @Column(type="int") */
    #[Column(type: 'int')]
    public int $salary;
}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures20;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Inheritance\SingleTable as InheritanceSingleTable;

/**
 * @Entity
 * @InheritanceSingleTable(value="foo_customer")
 */
#[Entity]
#[InheritanceSingleTable(value: 'foo_customer')]
class Customer extends Person
{
    /** @Column(type="string") */
    #[Column(type: 'string')]
    public string $preferences;
}

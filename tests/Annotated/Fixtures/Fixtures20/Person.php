<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures20;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Inheritance\DiscriminatorColumn;

/**
 * @Entity
 * @DiscriminatorColumn(name="type")
 */
#[Entity]
#[DiscriminatorColumn(name: 'type')]
class Person
{
    /** @Column(type="string") */
    #[Column(type: 'string')]
    public string $name;

    /** @Column(type="string") */
    #[Column(type: 'string')]
    public string $type;

    /** @Column(type="primary", name="id") */
    #[Column(type: 'primary', name: 'id')]
    protected int $foo_id;
}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures21;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;

/**
 * @Entity
 */
#[Entity]
class Person
{
    /** @Column(type="string") */
    #[Column(type: 'string')]
    public string $name;

    /** @Column(type="string") */
    #[Column(type: 'string')]
    public string $type;

    /** @Column(type="primary") */
    #[Column(type: 'primary')]
    protected int $id;
}

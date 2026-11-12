<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures1;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;

/**
 * @Entity()
 */
#[Entity]
class Child extends Simple
{
    /** @Column(type="string") */
    #[Column(type: 'string')]
    protected $name;
}

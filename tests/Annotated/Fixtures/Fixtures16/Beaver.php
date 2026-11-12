<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures16;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;

/**
 * @Entity
 */
#[Entity]
class Beaver extends Person
{
    use ExtraColumns;

    /** @Column(type="int") */
    #[Column(type: 'int')]
    protected int $teethAmount;
}

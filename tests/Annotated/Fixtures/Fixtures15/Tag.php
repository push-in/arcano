<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures15;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;

/**
 * @Entity()
 */
#[Entity]
class Tag
{
    /** @Column(type="integer", primary=true) */
    #[Column(type: 'integer', primary: true)]
    protected int $id1;

    /** @Column(type="integer", primary=true) */
    #[Column(type: 'integer', primary: true)]
    protected int $id2;

    /** @Column(type="string") */
    #[Column(type: 'string')]
    protected string $name = '';
}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures14;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;

/**
 * @Entity()
 */
#[Entity]
class Post
{
    /** @Column(type="integer", primary=true) */
    #[Column(type: 'integer', primary: true)]
    protected int $id1;

    /** @Column(type="integer", primary=true) */
    #[Column(type: 'integer', primary: true)]
    protected int $id2;
}

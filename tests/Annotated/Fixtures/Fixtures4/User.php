<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures4;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;

/**
 * @Entity()
 */
#[Entity]
class User
{
    /** @Column */
    #[Column]
    protected $id;
}

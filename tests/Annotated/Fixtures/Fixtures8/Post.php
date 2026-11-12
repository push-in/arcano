<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures8;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;

/**
 * @Entity()
 */
#[Entity]
class Post
{
    /** @Column(type="primary") */
    #[Column(type: 'primary')]
    protected $id;
}

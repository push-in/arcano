<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures1\Tag;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;

/**
 * @Entity(role="tagContext")
 */
#[Entity(role: 'tagContext')]
class Context
{
    /** @Column(type="primary") */
    #[Column(type: 'primary')]
    protected $id;
}

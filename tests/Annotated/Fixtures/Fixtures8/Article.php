<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures8;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;

/**
 * @Entity()
 */
#[Entity]
class Article extends Post
{
    /** @Column(type="string") */
    #[Column(type: 'string')]
    public $title;
}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\RefersToMorphed;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;

/**
 * @Entity
 */
#[Entity]
class Post implements MorphedParentInterface
{
    /** @Column(type="primary") */
    #[Column(type: 'primary')]
    protected $id;

    /** @Column(type="string") */
    #[Column(type: 'string')]
    protected $title;
}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\RefersToMorphed;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Relation\Morphed\RefersToMorphed;

/**
 * @Entity
 */
#[Entity]
class Comment
{
    /** @Column(type="primary") */
    #[Column(type: 'primary')]
    protected $id;

    /** @Column(type="string") */
    #[Column(type: 'string')]
    protected $message;

    /** @RefersToMorphed(target="MorphedParentInterface") */
    #[RefersToMorphed(target: 'MorphedParentInterface')]
    protected $parent;
}

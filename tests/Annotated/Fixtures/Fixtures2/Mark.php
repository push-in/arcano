<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures2;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Relation\Inverse;
use Arcano\Annotated\Annotation\Relation\Morphed\BelongsToMorphed;

/**
 * @Entity()
 */
#[Entity]
class Mark
{
    /** @Column(type="primary") */
    #[Column(type: 'primary')]
    protected $id;

    /** @BelongsToMorphed(target="MarkedInterface", inverse=@Inverse(as="mark", type="morphedHasOne")) */
    #[BelongsToMorphed(target: 'MarkedInterface')]
    #[Inverse(as: 'mark', type: 'morphedHasOne')]
    protected $owner;
}

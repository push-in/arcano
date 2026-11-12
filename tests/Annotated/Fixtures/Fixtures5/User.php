<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures5;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Relation\HasOne;
use Arcano\Annotated\Annotation\Relation\Inverse;

/**
 * @Entity()
 */
#[Entity]
class User
{
    /** @Column(type="primary") */
    #[Column(type: 'primary')]
    protected $id;

    /**
     * @HasOne(target=Simple::class, load="eager")
     * @Inverse(as="user", type="belongsTo", load="lazy")
     */
    #[HasOne(target: Simple::class, load: 'eager')]
    #[Inverse(as: 'user', type: 'belongsTo', load: 'lazy')]
    protected $simple;
}

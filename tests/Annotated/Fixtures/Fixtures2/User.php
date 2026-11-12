<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures2;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Relation\HasOne;
use Arcano\Annotated\Annotation\Relation\Inverse;

/**
 * @Entity()
 */
#[Entity]
class User implements MarkedInterface
{
    /** @Column(type="primary") */
    #[Column(type: 'primary')]
    protected $id;

    /** @HasOne(target="Simple", inverse=@Inverse(as="user", type="belongsTo")) */
    #[HasOne(target: 'Simple')]
    #[Inverse(as: 'user', type: 'belongsTo')]
    protected $simple;
}

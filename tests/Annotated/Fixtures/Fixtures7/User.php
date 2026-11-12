<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures7;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Relation\Embedded;

/**
 * @Entity()
 */
#[Entity]
class User
{
    /** @Column(type="primary") */
    #[Column(type: 'primary')]
    protected $id;

    /** @Embedded(target="Address", load="lazy") */
    #[Embedded(target: 'Address', load: 'lazy')]
    protected $address;
}

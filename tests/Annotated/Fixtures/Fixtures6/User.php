<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures6;

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

    /** @Embedded(target=Address::class) */
    #[Embedded(target: Address::class)]
    protected $address;

    /** @Embedded(target=Address::class, prefix="work_address_") */
    #[Embedded(target: Address::class, prefix: 'work_address_')]
    protected Address $workAddress;
}

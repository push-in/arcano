<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures19;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;

/**
 * @Entity
 */
#[Entity]
class Booking
{
    /** @Column(type="primary") */
    #[Column(type: 'primary')]
    protected ?int $bid = null;

    /** @Column(type="string", typecast="Arcano\Annotated\Tests\Fixtures\Fixtures19\BackedEnum") */
    #[Column(type: 'string', typecast: BackedEnum::class)]
    protected ?BackedEnum $be = null;

    /** @Column(type="string", typecast="Arcano\Annotated\Tests\Fixtures\Fixtures19\BackedEnumWrapper") */
    #[Column(type: 'string', typecast: BackedEnumWrapper::class)]
    protected ?BackedEnumWrapper $bew = null;
}

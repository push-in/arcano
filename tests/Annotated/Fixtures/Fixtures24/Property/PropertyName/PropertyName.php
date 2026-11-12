<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures24\Property\PropertyName;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\ForeignKey;

/**
 * @Entity(role="from", table="from")
 */
#[Entity(role: 'from', table: 'from')]
class PropertyName
{
    /**
     * @Column(type="primary")
     */
    #[Column(type: 'primary')]
    public int $id;

    /**
     * @Column(type="integer", name="inner_key")
     * @ForeignKey(target="target", outerKey="outerKey", innerKey="innerKey")
     */
    #[Column(type: 'integer', name: 'inner_key')]
    #[ForeignKey(target: Target::class, outerKey: 'outerKey', innerKey: 'innerKey')]
    public int $innerKey;
}

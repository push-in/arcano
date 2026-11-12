<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures24\Class\PropertyName;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\ForeignKey;

/**
 * @Entity(role="from", table="from")
 * @ForeignKey(target="target", outerKey="outerKey", innerKey="innerKey")
 */
#[ForeignKey(target: Target::class, innerKey: 'innerKey', outerKey: 'outerKey')]
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
     */
    #[Column(type: 'integer', name: 'inner_key')]
    public int $innerKey;
}

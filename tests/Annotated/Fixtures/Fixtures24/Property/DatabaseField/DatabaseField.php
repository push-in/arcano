<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures24\Property\DatabaseField;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\ForeignKey;

/**
 * @Entity(role="from", table="from")
 */
#[Entity(role: 'from', table: 'from')]
class DatabaseField
{
    /**
     * @Column(type="primary")
     */
    #[Column(type: 'primary')]
    public int $id;

    /**
     * @Column(type="integer", name="inner_key")
     * @ForeignKey(target="target", outerKey="outer_key", innerKey="inner_key")
     */
    #[Column(type: 'integer', name: 'inner_key')]
    #[ForeignKey(target: Target::class, outerKey: 'outer_key', innerKey: 'inner_key')]
    public int $innerKey;
}

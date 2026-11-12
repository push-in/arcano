<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures24\Class\PrimaryKey;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\ForeignKey;

/**
 * @Entity(role="from", table="from")
 * @ForeignKey(target="target", outerKey="id", innerKey="inner_key")
 */
#[ForeignKey(target: Target::class, outerKey: 'id', innerKey: 'inner_key')]
#[Entity(role: 'from', table: 'from')]
class PrimaryKey
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

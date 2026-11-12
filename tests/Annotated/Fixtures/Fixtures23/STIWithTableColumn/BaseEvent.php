<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures23\STIWithTableColumn;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Inheritance\DiscriminatorColumn;
use Arcano\Annotated\Annotation\Table;

/**
 * @Entity
 * @Table(columns={
 *     @Column(type="primary", property="id"),
 *     @Column(type="string", property="action")
 * })
 * @DiscriminatorColumn(name="action")
 */
#[Entity]
#[Table(columns: [
    new Column(type: 'primary', property: 'id'),
    new Column(type: 'string', property: 'action'),
])]
#[DiscriminatorColumn('action')]
abstract class BaseEvent
{
    public int $id;
    public string $action;
}

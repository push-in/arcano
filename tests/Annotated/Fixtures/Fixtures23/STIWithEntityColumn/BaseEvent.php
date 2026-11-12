<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures23\STIWithEntityColumn;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Inheritance\DiscriminatorColumn;

/**
 * @Entity(columns={
 *     @Column(type="primary", property="id"),
 *     @Column(type="string", property="action")
 * })
 * @DiscriminatorColumn(name="action")
 */
#[Entity(columns: [
    new Column(type: 'primary', property: 'id'),
    new Column(type: 'string', property: 'action'),
])]
#[DiscriminatorColumn(name: 'action')]
abstract class BaseEvent
{
    public int $id;
    public string $action;
}

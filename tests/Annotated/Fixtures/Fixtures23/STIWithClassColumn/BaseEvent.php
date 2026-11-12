<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures23\STIWithClassColumn;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Inheritance\DiscriminatorColumn;

/**
 * @Entity
 * @Column(type="primary", property="id")
 * @Column(type="string", property="action")
 * @DiscriminatorColumn(name="action")
 */
#[Entity]
#[Column(type: 'primary', property: 'id')]
#[Column(type: 'string', property: 'action')]
#[DiscriminatorColumn(name: 'action')]
abstract class BaseEvent
{
    public int $id;
    public string $action;
}

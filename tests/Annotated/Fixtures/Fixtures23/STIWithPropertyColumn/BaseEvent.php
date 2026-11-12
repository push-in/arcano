<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures23\STIWithPropertyColumn;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Inheritance\DiscriminatorColumn;

/**
 * @Entity
 * @DiscriminatorColumn(name="action")
 */
#[Entity]
#[DiscriminatorColumn(name: 'action')]
abstract class BaseEvent
{
    /**
     * @Column(type="primary")
     */
    #[Column(type: 'primary')]
    public int $id;

    /**
     * @Column(type="string")
     */
    #[Column(type: 'string')]
    public string $action;
}

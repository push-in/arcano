<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures23\STIWithPropertyColumn;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;

/**
 * @Entity
 */
#[Entity]
final class Comment implements EventEmitterInterface
{
    /**
     * @Column(type="primary")
     */
    #[Column(type: 'primary')]
    public int $id;

    /**
     * @Column(type="string", nullable=true)
     */
    #[Column(type: 'string', nullable: true)]
    public ?string $body = null;
}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures15;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Table\Index;

/**
 * @Entity()
 * @Index(name="puindex", columns={"id1", "id2", "id3", "id4", "deleted_at"}, unique=true)
 */
#[Entity]
#[Index(name: 'puindex', columns: ['id1', 'id2', 'id3', 'id4', 'deleted_at'], unique: true)]
class Context
{
    /** @Column(type="primary") */
    #[Column(type: 'primary')]
    protected int $id;

    /** @Column(type="integer") */
    #[Column(type: 'integer')]
    protected int $id1;

    /** @Column(type="integer") */
    #[Column(type: 'integer')]
    protected int $id2;

    /** @Column(type="integer") */
    #[Column(type: 'integer')]
    protected int $id3;

    /** @Column(type="integer") */
    #[Column(type: 'integer')]
    protected int $id4;

    /** @Column(type="datetime", nullable=true) */
    #[Column(type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $deleted_at = null;
}

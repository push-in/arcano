<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures24\Entity\DatabaseField;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;

/**
 * @Entity(table="to")
 */
class Target
{
    /**
     * @Column(type="primary")
     */
    public int $id;

    /**
     * @Column(type="integer", name="outer_key")
     */
    public int $outerKey;
}

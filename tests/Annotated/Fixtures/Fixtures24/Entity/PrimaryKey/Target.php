<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures24\Entity\PrimaryKey;

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
}

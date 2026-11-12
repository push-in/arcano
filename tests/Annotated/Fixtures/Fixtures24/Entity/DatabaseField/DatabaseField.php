<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures24\Entity\DatabaseField;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\ForeignKey;

/**
 * @Entity(
 *     role="from",
 *     table="from",
 *     foreignKeys={
 *         @ForeignKey(target="target", outerKey="outer_key", innerKey="inner_key")
 *     }
 * )
 */
class DatabaseField
{
    /**
     * @Column(type="primary")
     */
    public int $id;

    /**
     * @Column(type="integer", name="inner_key")
     */
    public int $innerKey;
}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures24\Entity\PrimaryKey;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\ForeignKey;

/**
 * @Entity(
 *     role="from",
 *     table="from",
 *     foreignKeys={
 *         @ForeignKey(target="target", outerKey="id", innerKey="inner_key")
 *     }
 * )
 */
class PrimaryKey
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

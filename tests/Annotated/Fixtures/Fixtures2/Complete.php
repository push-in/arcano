<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures2;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Relation\BelongsTo;
use Arcano\Annotated\Annotation\Relation\Inverse;

/**
 * @Entity(role = "eComplete")
 */
#[Entity(role: 'eComplete')]
class Complete
{
    /** @Column(type="primary") */
    #[Column(type: 'primary')]
    protected $id;

    /** @BelongsTo(target="Simple", fkAction="NO ACTION", inverse=@Inverse(as="child", type="hasOne")) */
    #[BelongsTo(target: 'Simple', fkAction: 'NO ACTION')]
    #[Inverse(as: 'child', type: 'hasOne')]
    protected $parent;

    /** @belongsTo(
     *     target="Simple",
     *     fkAction="NO ACTION",
     *     innerKey="uncle_id",
     *     inverse=@Inverse(as="stepKids", type="hasMany")
     * )
     */
    #[BelongsTo(target: 'Simple', fkAction: 'NO ACTION', innerKey: 'uncle_id')]
    #[Inverse(as: 'stepKids', type: 'hasMany')]
    protected $uncles;
}

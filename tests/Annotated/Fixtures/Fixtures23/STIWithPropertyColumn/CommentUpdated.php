<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures23\STIWithPropertyColumn;

use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Inheritance\SingleTable;
use Arcano\Annotated\Annotation\Relation\Morphed\BelongsToMorphed;

/**
 * @Entity
 * @SingleTable(value="comment.updated")
 */
#[Entity]
#[SingleTable(value: 'comment.updated')]
final class CommentUpdated extends BaseEvent
{
    /**
     * @BelongsToMorphed(
     *     target="EventEmitterInterface",
     *     innerKey="object_id",
     *     morphKey="object_type",
     *     indexCreate=false
     * )
     */
    #[BelongsToMorphed(
        target: EventEmitterInterface::class,
        innerKey: 'object_id',
        morphKey: 'object_type',
        indexCreate: false,
    )]
    public EventEmitterInterface|Comment $object;
}

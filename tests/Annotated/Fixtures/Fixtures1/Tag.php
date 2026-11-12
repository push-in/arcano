<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures1;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Relation\Morphed\MorphedHasOne;

/**
 * @Entity(
 *     typecast={
 *      Typecast\Typecaster::class,
 *      "Arcano\Annotated\Tests\Fixtures\Fixtures1\Typecast\UuidTypecaster",
 *      "foo"
 *     }
 * )
 */
#[Entity(typecast: [Typecast\Typecaster::class, Typecast\UuidTypecaster::class, 'foo'])]
class Tag
{
    /** @Column(type="primary") */
    #[Column(type: 'primary')]
    protected $id;

    /** @MorphedHasOne(target="Label", outerKey="owner_id", morphKey="owner_role", indexCreate=false) */
    #[MorphedHasOne(target: 'Label', outerKey: 'owner_id', morphKey: 'owner_role', indexCreate: false)]
    protected $label;
}

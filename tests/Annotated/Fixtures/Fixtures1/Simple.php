<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures1;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Relation\HasMany;
use Arcano\Annotated\Annotation\Relation\HasOne;
use Arcano\Annotated\Annotation\Relation\Morphed\MorphedHasMany;
use Arcano\Annotated\Annotation\Relation\RefersTo;

/**
 * @Entity(
 *     role="simple",
 *     typecast="Arcano\Annotated\Tests\Fixtures\Fixtures1\Typecast\Typecaster"
 * )
 */
#[Entity(typecast: Typecast\Typecaster::class)]
class Simple implements LabelledInterface
{
    /**
     * @Column(type="primary", default="xx")
     *
     * @var int
     */
    #[Column(type: 'primary', default: 'xx')]
    protected $id;

    /**
     * @HasOne(target="Complete")
     *
     * @var Complete
     */
    #[HasOne(target: 'Complete')]
    protected $one;

    /**
     * @HasMany(
     *     target="WithTable",
     *     where={"id": {">=": 1}},
     *     orderBy={"id": "DESC"},
     *     collection="bar"
     * )
     *
     * @var Collection\BaseCollection|WithTable[]
     */
    #[HasMany(target: 'WithTable', where: ['id' => ['>=' => 1]], orderBy: ['id' => 'DESC'], collection: 'bar')] // phpcs:ignore
    protected $many;

    /**
     * @RefersTo(target="Simple", fkAction="NO ACTION")
     */
    #[RefersTo(target: 'Simple', fkAction: 'NO ACTION')]
    protected $parent;

    /**
     * @MorphedHasMany(
     *     target="Label",
     *     outerKey="owner_id",
     *     morphKey="owner_role",
     *     indexCreate=false,
     *     collection=Arcano\Annotated\Tests\Fixtures\Fixtures1\Collection\BaseCollection::class
     * )
     */
    #[MorphedHasMany(target: 'Label', outerKey: 'owner_id', morphKey: 'owner_role', indexCreate: false, collection: Collection\BaseCollection::class)] // phpcs:ignore
    protected $labels;

    /**
     * @Column(type="string", readonlySchema=true)
     */
    #[Column(type: 'string', readonlySchema: true)]
    protected string $readOnlyColumn;
}

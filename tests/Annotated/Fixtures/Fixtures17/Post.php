<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures17;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Tests\Fixtures\Fixtures1\Child;

/**
 * @Entity
 * @ParentSegmentSchemaModifier(parent=\stdClass::class)
 * @MapperSegmentSchemaModifier(class=Child::class)
 */
#[Entity]
#[ParentSegmentSchemaModifier(parent: \stdClass::class)]
#[MapperSegmentSchemaModifier(class: Child::class)]
class Post
{
    /** @Column(type="integer", primary=true) */
    #[Column(type: 'integer', primary: true)]
    protected int $id1;

    /** @Column(type="string") */
    #[Column(type: 'string')]
    protected string $name;
}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures8;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Relation\HasOne;

/**
 * @Entity()
 */
#[Entity]
class Some
{
    /** @Column(type="primary") */
    #[Column(type: 'primary')]
    protected $id;

    /** @HasOne(target="Article", innerKey="id") */
    #[HasOne(target: 'Article', innerKey: 'id')]
    protected $article;
}

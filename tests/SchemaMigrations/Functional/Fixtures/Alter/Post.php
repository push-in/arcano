<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Arcano\Schema\Generator\Migrations\Tests\Functional\Fixtures\Alter;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Relation\BelongsTo;

#[Entity]
class Post
{
    #[Column(type: 'primary')]
    protected int $id;

    #[BelongsTo(target: Other::class, nullable: false)]
    protected Other $other;
}

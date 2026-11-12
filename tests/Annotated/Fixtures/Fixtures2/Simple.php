<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures2;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;

/**
 * @Entity()
 */
#[Entity]
class Simple implements MarkedInterface
{
    /** @Column(type="primary") */
    #[Column(type: 'primary')]
    protected $id;
}

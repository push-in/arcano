<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures27;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;

#[Entity]
class SoSeparate extends SoSti
{
    #[Column(type: 'string')]
    public string $extra;
}

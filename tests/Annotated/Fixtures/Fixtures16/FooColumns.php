<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures16;

use Arcano\Annotated\Annotation\Column;

trait FooColumns
{
    /** @Column(type="string") */
    #[Column(type: 'string')]
    public string $bar;
}

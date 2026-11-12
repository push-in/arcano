<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Unit\Attribute\SingleTable;

enum StringEnum: string
{
    case A = 'a';
    case B = 'b';
}

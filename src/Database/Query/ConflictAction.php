<?php

declare(strict_types=1);

namespace Arcano\Database\Query;

enum ConflictAction
{
    case Update;
    case Nothing;
}

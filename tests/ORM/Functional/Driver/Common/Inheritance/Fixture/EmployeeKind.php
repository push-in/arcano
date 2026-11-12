<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Inheritance\Fixture;

enum EmployeeKind: int
{
    case Employee = 1;
    case Manager = 2;
}

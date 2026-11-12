<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Fixtures;

use Arcano\Database\Query\InsertQuery;

abstract class TestInsertCommandWithReturning extends InsertQuery implements \Arcano\Database\Query\ReturningInterface {}

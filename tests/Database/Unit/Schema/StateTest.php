<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Unit\Schema;

use Arcano\Database\Driver\Postgres\Schema\PostgresColumn;
use Arcano\Database\Schema\State;
use PHPUnit\Framework\TestCase;

final class StateTest extends TestCase
{
    public function testGetPrimaryKeys(): void
    {
        $state = new State('foo');

        $state->registerColumn((new PostgresColumn('foo', 'smallPrimary'))->smallPrimary());
        $state->registerColumn((new PostgresColumn('foo', 'primary'))->primary());
        $state->registerColumn((new PostgresColumn('foo', 'bigPrimary'))->bigPrimary());
        $state->registerColumn((new PostgresColumn('foo', 'string'))->string());
        $state->registerColumn((new PostgresColumn('foo', 'integer'))->integer());

        $state->setPrimaryKeys(['foo', 'bar']);

        $this->assertSame(['foo', 'bar', 'smallPrimary', 'primary', 'bigPrimary'], $state->getPrimaryKeys());
    }
}

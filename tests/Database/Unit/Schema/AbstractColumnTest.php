<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Unit\Schema;

use Arcano\Database\Schema\AbstractColumn;
use PHPUnit\Framework\TestCase;

final class AbstractColumnTest extends TestCase
{
    private AbstractColumn $column;

    public function testReadonlySchemaFalseByDefault(): void
    {
        $this->assertFalse($this->column->isReadonlySchema());
    }

    public function testReadonlySchema(): void
    {
        $this->assertFalse($this->column->isReadonlySchema());

        $this->column->setAttributes(['readonlySchema' => true]);

        $this->assertTrue($this->column->isReadonlySchema());
    }

    protected function setUp(): void
    {
        $this->column = new class('foo', 'bar') extends AbstractColumn {};
    }
}

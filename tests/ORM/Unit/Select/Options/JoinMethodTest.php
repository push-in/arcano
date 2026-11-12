<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Unit\Select\Options;

use Arcano\ORM\Select\JoinableLoader;
use Arcano\ORM\Select\Options\JoinMethod;
use PHPUnit\Framework\TestCase;

final class JoinMethodTest extends TestCase
{
    public function testInnerJoinValue(): void
    {
        $this->assertSame(JoinableLoader::JOIN, JoinMethod::InnerJoin->value);
    }

    public function testLeftJoinValue(): void
    {
        $this->assertSame(JoinableLoader::LEFT_JOIN, JoinMethod::LeftJoin->value);
    }

    public function testCaseCount(): void
    {
        $this->assertCount(2, JoinMethod::cases());
    }

    public function testFromValue(): void
    {
        $this->assertSame(JoinMethod::InnerJoin, JoinMethod::from(JoinableLoader::JOIN));
        $this->assertSame(JoinMethod::LeftJoin, JoinMethod::from(JoinableLoader::LEFT_JOIN));
    }
}

<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Unit\Select\Options;

use Arcano\ORM\Select;
use Arcano\ORM\Select\JoinableLoader;
use Arcano\ORM\Select\Options\LoadMethod;
use PHPUnit\Framework\TestCase;

final class LoadMethodTest extends TestCase
{
    public function testSingleQueryValue(): void
    {
        $this->assertSame(Select::SINGLE_QUERY, LoadMethod::SingleQuery->value);
        $this->assertSame(JoinableLoader::INLOAD, LoadMethod::SingleQuery->value);
    }

    public function testOuterQueryValue(): void
    {
        $this->assertSame(Select::OUTER_QUERY, LoadMethod::OuterQuery->value);
        $this->assertSame(JoinableLoader::POSTLOAD, LoadMethod::OuterQuery->value);
    }

    public function testCaseCount(): void
    {
        $this->assertCount(2, LoadMethod::cases());
    }

    public function testFromValue(): void
    {
        $this->assertSame(LoadMethod::SingleQuery, LoadMethod::from(JoinableLoader::INLOAD));
        $this->assertSame(LoadMethod::OuterQuery, LoadMethod::from(JoinableLoader::POSTLOAD));
    }
}

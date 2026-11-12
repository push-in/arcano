<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Unit\Attribute\SingleTable;

use Arcano\Annotated\Annotation\Inheritance\SingleTable;
use PHPUnit\Framework\TestCase;

final class SingleTableTest extends TestCase
{
    public static function dataBase(): iterable
    {
        yield [null, null];
        yield ['test', 'test'];
        yield ['42', 42];
        yield ['36.6', 36.6];
        yield ['test', new StringableObject('test')];
        yield ['1', IntegerEnum::ONE];
        yield ['a', StringEnum::A];
    }

    /**
     * @dataProvider dataBase
     */
    public function testBase1(?string $expected, mixed $value): void
    {
        $attribute = new SingleTable($value);

        $this->assertSame($expected, $attribute->getValue());
    }
}

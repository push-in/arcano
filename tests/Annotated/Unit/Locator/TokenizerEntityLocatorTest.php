<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Unit\Locator;

use Arcano\Annotated\Annotation\Entity as Attribute;
use Arcano\Annotated\Locator\Entity;
use Arcano\Annotated\Locator\TokenizerEntityLocator;
use Arcano\Annotated\Tests\Fixtures\Fixtures1\AnotherClass;
use Arcano\Annotated\Tests\Fixtures\Fixtures1\Tag;
use Arcano\Annotated\Tests\Fixtures\Fixtures1\Typecast\Typecaster;
use Arcano\Annotated\Tests\Fixtures\Fixtures1\Typecast\UuidTypecaster;
use Arcano\Annotated\Tests\Fixtures\Fixtures1\WithTable;
use PHPUnit\Framework\TestCase;
use Spiral\Tokenizer\ClassesInterface;

final class TokenizerEntityLocatorTest extends TestCase
{
    public static function classesDataProvider(): \Traversable
    {
        yield [[], []];
        yield [[], [AnotherClass::class => new \ReflectionClass(AnotherClass::class)]];
        yield [
            [
                new Entity(
                    new Attribute(typecast: [Typecaster::class, UuidTypecaster::class, 'foo']),
                    new \ReflectionClass(Tag::class),
                ),
                new Entity(
                    new Attribute(),
                    new \ReflectionClass(WithTable::class),
                ),
            ],
            [
                Tag::class => new \ReflectionClass(Tag::class),
                WithTable::class => new \ReflectionClass(WithTable::class),
            ],
        ];
    }

    /**
     * @dataProvider classesDataProvider
     */
    public function testGetEntities(array $expected, array $classes): void
    {
        $mock = $this->createMock(ClassesInterface::class);
        $mock->method('getClasses')->willReturn($classes);

        $locator = new TokenizerEntityLocator($mock);

        $this->assertEquals($expected, $locator->getEntities());
    }
}

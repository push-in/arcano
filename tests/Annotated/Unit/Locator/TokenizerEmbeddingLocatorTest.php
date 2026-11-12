<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Unit\Locator;

use Arcano\Annotated\Annotation\Embeddable;
use Arcano\Annotated\Locator\Embedding;
use Arcano\Annotated\Locator\TokenizerEmbeddingLocator;
use Arcano\Annotated\Tests\Fixtures\Fixtures1\Child;
use Arcano\Annotated\Tests\Fixtures\Fixtures7\Address;
use PHPUnit\Framework\TestCase;
use Spiral\Tokenizer\ClassesInterface;

final class TokenizerEmbeddingLocatorTest extends TestCase
{
    public static function classesDataProvider(): \Traversable
    {
        yield [[], []];
        yield [[], [Child::class => new \ReflectionClass(Child::class)]];
        yield [
            [
                new Embedding(
                    new Embeddable(role: 'address', columnPrefix: 'address_'),
                    new \ReflectionClass(Address::class),
                ),
            ],
            [
                Address::class => new \ReflectionClass(Address::class),
                Child::class => new \ReflectionClass(Child::class),
            ],
        ];
    }

    /**
     * @dataProvider classesDataProvider
     */
    public function testGetEmbeddings(array $expected, array $classes): void
    {
        $mock = $this->createMock(ClassesInterface::class);
        $mock->method('getClasses')->willReturn($classes);

        $locator = new TokenizerEmbeddingLocator($mock);

        $this->assertEquals($expected, $locator->getEmbeddings());
    }
}

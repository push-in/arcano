<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Postgres;

// phpcs:ignore
use Arcano\Annotated\Entities;
use Arcano\Annotated\Locator\TokenizerEntityLocator;
use Arcano\Annotated\Tests\Functional\Driver\Common\GeneratedFieldsTestCase;
use Arcano\ORM\Schema\GeneratedField;
use Arcano\ORM\SchemaInterface;
use Arcano\Schema\Compiler;
use Arcano\Schema\Registry;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\Tokenizer;

/**
 * @group driver
 * @group driver-postgres
 */
final class GeneratedFieldsTest extends GeneratedFieldsTestCase
{
    public const DRIVER = 'postgres';

    /**
     * @dataProvider allReadersProvider
     */
    public function testSerialGeneratedFields(ReaderInterface $reader): void
    {
        $tokenizer = new Tokenizer(new TokenizerConfig([
            'directories' => [__DIR__ . '/../../../Fixtures/Fixtures25/PostgreSQL'],
            'exclude' => [],
        ]));

        $locator = $tokenizer->classLocator();

        $r = new Registry($this->dbal);

        $schema = (new Compiler())->compile($r, [
            new Entities(new TokenizerEntityLocator($locator, $reader), $reader),
        ]);

        $this->assertSame(
            [
                'id' => GeneratedField::ON_INSERT,
                'smallSerial' => GeneratedField::ON_INSERT,
                'serial' => GeneratedField::ON_INSERT,
                'bigSerial' => GeneratedField::ON_INSERT,
            ],
            $schema['withGeneratedSerial'][SchemaInterface::GENERATED_FIELDS],
        );
    }
}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Functional\Driver\Common;

use Arcano\Annotated\Entities;
use Arcano\Annotated\Locator\TokenizerEntityLocator;
use Arcano\ORM\Schema\GeneratedField;
use Arcano\ORM\SchemaInterface;
use Arcano\Schema\Compiler;
use Arcano\Schema\Registry;
use Spiral\Attributes\ReaderInterface;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\Tokenizer;

abstract class GeneratedFieldsTestCase extends BaseTestCase
{
    /**
     * @dataProvider allReadersProvider
     */
    public function testGeneratedFields(ReaderInterface $reader): void
    {
        $tokenizer = new Tokenizer(new TokenizerConfig([
            'directories' => [__DIR__ . '/../../../Fixtures/Fixtures25'],
            'exclude' => ['PostgreSQL'],
        ]));

        $locator = $tokenizer->classLocator();

        $r = new Registry($this->dbal);
        $schema = (new Compiler())->compile($r, [
            new Entities(new TokenizerEntityLocator($locator, $reader), $reader),
        ]);

        $this->assertSame(
            [
                'id' => GeneratedField::ON_INSERT,
                'createdAt' => GeneratedField::BEFORE_INSERT,
                'createdAtGeneratedByDatabase' => GeneratedField::ON_INSERT,
                'updatedAt' => GeneratedField::BEFORE_INSERT | GeneratedField::BEFORE_UPDATE,
            ],
            $schema['withGeneratedFields'][SchemaInterface::GENERATED_FIELDS],
        );
    }
}

<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Relation;

use Arcano\ORM\Relation;
use Arcano\Schema\Exception\OptionException;
use Arcano\Schema\Relation\OptionSchema;
use Arcano\Schema\Relation\RelationSchema;
use PHPUnit\Framework\TestCase;

class OptionSchemaTest extends TestCase
{
    public function testAliases(): void
    {
        $options = new OptionSchema([
            'alias' => Relation::TYPE,
        ]);

        $options = $options->withTemplate([
            Relation::TYPE => 200,
        ]);

        $this->assertSame(200, $options->get(Relation::TYPE));

        $options = $options->withOptions([
            'alias' => 100,
        ]);

        $this->assertSame(100, $options->get(Relation::TYPE));
    }

    public function testInvalidAlias(): void
    {
        $options = new OptionSchema([
            'alias' => Relation::TYPE,
        ]);

        $options = $options->withTemplate([
            Relation::TYPE => 200,
        ]);

        $this->expectException(OptionException::class);

        $options->withOptions([
            'unknown' => 100,
        ]);
    }

    public function testInvalidAlias2(): void
    {
        $options = new OptionSchema([
            'alias' => Relation::TYPE,
        ]);

        $options = $options->withTemplate([
            Relation::TYPE => 200,
        ])->withOptions([
            'alias' => 100,
        ]);

        $this->expectException(OptionException::class);

        $options->get(RelationSchema::FK_ACTION);
    }

    public function testDebugInfo(): void
    {
        $options = new OptionSchema([
            'alias' => Relation::TYPE,
        ]);

        $options = $options->withTemplate([
            Relation::TYPE => 200,
        ])->withOptions([
            'alias' => 100,
        ]);

        $this->assertSame([
            'alias' => 100,
        ], $options->__debugInfo());
    }
}

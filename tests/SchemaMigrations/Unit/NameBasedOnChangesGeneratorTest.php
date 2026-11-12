<?php

declare(strict_types=1);

namespace Arcano\Schema\Generator\Migrations\Tests\Unit;

use Arcano\Database\Schema\AbstractTable;
use Arcano\Database\Schema\ComparatorInterface;
use Arcano\Migrations\Atomizer\Atomizer;
use Arcano\Migrations\Atomizer\RendererInterface;
use Arcano\Schema\Generator\Migrations\NameBasedOnChangesGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \Arcano\Schema\Generator\Migrations\NameBasedOnChangesGenerator
 */
final class NameBasedOnChangesGeneratorTest extends TestCase
{
    /**
     * @covers ::generate
     */
    public function testGenerate(): void
    {
        $atomizer = new Atomizer(
            $this->createMock(RendererInterface::class),
        );

        $create = $this->createMock(AbstractTable::class);
        $create->method('getName')->willReturn('creates');
        $create->method('getStatus')->willReturn(AbstractTable::STATUS_NEW);

        $drops = $this->createMock(AbstractTable::class);
        $drops->method('getName')->willReturn('drops');
        $drops->method('getStatus')->willReturn(AbstractTable::STATUS_DECLARED_DROPPED);

        $renamesCmp = $this->createMock(ComparatorInterface::class);
        $renamesCmp->method('isRenamed')->willReturn(true);
        $renames = $this->createMock(AbstractTable::class);
        $renames->method('getName')->willReturn('renames');
        $renames->method('getStatus')->willReturn(AbstractTable::STATUS_EXISTS);
        $renames->method('getComparator')->willReturn($renamesCmp);
        $renames->method('getInitialName')->willReturn('old_name');

        $atomizer
            ->addTable($create)
            ->addTable($drops)
            ->addTable($renames);
        $generator = new NameBasedOnChangesGenerator();
        self::assertSame(
            'create_creates_drop_drops_rename_old_name',
            $generator->generate($atomizer),
        );
    }
}

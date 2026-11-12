<?php

declare(strict_types=1);

namespace Arcano\Schema\Generator\Migrations\Tests\Unit;

use Arcano\Migrations\Config\MigrationConfig;
use Arcano\Migrations\RepositoryInterface;
use Arcano\Schema\Generator\Migrations\Exception\GeneratorException;
use Arcano\Schema\Generator\Migrations\GenerateMigrations;
use Arcano\Schema\Generator\Migrations\NameGeneratorInterface;
use Arcano\Schema\Generator\Migrations\Strategy\MultipleFilesStrategy;
use PHPUnit\Framework\TestCase;

final class GenerateMigrationsTest extends TestCase
{
    public function testGenerateException(): void
    {
        $generator = new GenerateMigrations(
            $this->createMock(RepositoryInterface::class),
            new MigrationConfig(),
            new MultipleFilesStrategy(new MigrationConfig(), $this->createMock(NameGeneratorInterface::class)),
        );

        $ref = new \ReflectionMethod($generator, 'generate');

        $this->expectException(GeneratorException::class);
        $ref->invoke($generator, 'test', []);
    }
}

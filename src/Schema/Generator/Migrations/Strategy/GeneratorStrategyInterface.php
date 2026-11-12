<?php

declare(strict_types=1);

namespace Arcano\Schema\Generator\Migrations\Strategy;

use Arcano\Database\Schema\AbstractTable;
use Arcano\Schema\Generator\Migrations\MigrationImage;

interface GeneratorStrategyInterface
{
    /**
     * @param non-empty-string $database
     * @param array<AbstractTable> $tables
     *
     * @return array<MigrationImage>
     */
    public function generate(string $database, array $tables): array;
}

<?php

declare(strict_types=1);

namespace Arcano\Schema\Generator\Migrations;

use Arcano\Migrations\Atomizer\Atomizer;

interface NameGeneratorInterface
{
    public function generate(Atomizer $atomizer): string;
}

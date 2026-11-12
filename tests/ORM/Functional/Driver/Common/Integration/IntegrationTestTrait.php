<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Integration;

use Arcano\ORM\Schema;

trait IntegrationTestTrait
{
    private function loadSchema(string $file): void
    {
        $schema = include $file;
        \assert(\is_array($schema));
        $this->orm = $this->orm->withSchema(new Schema($schema));
    }
}

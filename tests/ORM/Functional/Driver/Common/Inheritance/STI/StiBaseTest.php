<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Inheritance\STI;

use Arcano\ORM\Collection\ArrayCollectionFactory;
use Arcano\ORM\Collection\DoctrineCollectionFactory;
use Arcano\ORM\Config\RelationConfig;
use Arcano\ORM\Factory;
use Arcano\ORM\Schema;
use Arcano\ORM\Tests\Functional\Driver\Common\BaseTest;
use Arcano\ORM\Tests\Traits\TableTrait;

abstract class StiBaseTest extends BaseTest
{
    use TableTrait;

    public function setUp(): void
    {
        parent::setUp();

        $factory = (new Factory(
            $this->dbal,
            RelationConfig::getDefault(),
            null,
            new ArrayCollectionFactory(),
        ))->withCollectionFactory('doctrine', new DoctrineCollectionFactory());

        $this->orm = $this->withSchema(new Schema($this->getSchemaArray()))->withFactory($factory);
    }

    abstract protected function getSchemaArray(): array;
}

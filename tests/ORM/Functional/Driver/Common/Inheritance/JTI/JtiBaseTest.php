<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Inheritance\JTI;

use Arcano\ORM\Collection\ArrayCollectionFactory;
use Arcano\ORM\Config\RelationConfig;
use Arcano\ORM\Factory;
use Arcano\ORM\Mapper\Mapper;
use Arcano\ORM\Schema;
use Arcano\ORM\Tests\Functional\Driver\Common\BaseTest;
use Arcano\ORM\Tests\Traits\TableTrait;

abstract class JtiBaseTest extends BaseTest
{
    use TableTrait;

    protected const DEFAULT_MAPPER = Mapper::class;

    public function setUp(): void
    {
        parent::setUp();

        $factory = new Factory(
            $this->dbal,
            RelationConfig::getDefault(),
            null,
            new ArrayCollectionFactory(),
        );
        $this->orm = $this->withSchema(new Schema($this->getSchemaArray()))->withFactory($factory);
    }

    abstract protected function getSchemaArray(): array;
}

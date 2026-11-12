<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\Common\Relation\HasOne;

abstract class HasOneOptionalRelationTest extends HasOneRelationTest
{
    protected const NULLABLE = true;
}

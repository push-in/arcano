<?php

declare(strict_types=1);

namespace Arcano\ORM\Tests\Functional\Driver\SQLite\Relation\Embedded;

// phpcs:ignore
use Arcano\ORM\Tests\Functional\Driver\Common\Relation\Embedded\EmbeddedCompositeKeyTest as CommonClass;

/**
 * @group driver
 * @group driver-sqlite
 */
class EmbeddedCompositeKeyTest extends CommonClass
{
    public const DRIVER = 'sqlite';
}

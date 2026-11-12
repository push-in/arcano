<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures16;

use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Inheritance\JoinedTable as InheritanceJoinedTable;

/**
 * @Entity
 * @InheritanceJoinedTable(outerKey="foo_id")
 */
#[Entity]
#[InheritanceJoinedTable(outerKey: 'foo_id')]
class Executive2 extends Executive {}

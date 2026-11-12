<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures20;

use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Inheritance\JoinedTable as InheritanceJoinedTable;

/**
 * @Entity
 * @InheritanceJoinedTable(fkCreate=false, outerKey="foo_id"))
 */
#[Entity]
#[InheritanceJoinedTable(fkCreate: false, outerKey: 'foo_id')]
class Buyer extends Person {}

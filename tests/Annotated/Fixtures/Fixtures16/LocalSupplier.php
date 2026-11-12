<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures16;

use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Inheritance\JoinedTable as InheritanceJoinedTable;

/**
 * @Entity(table="local_suppliers")
 * @InheritanceJoinedTable(outerKey="index_id")
 */
#[Entity(
    table: 'local_suppliers',
)]
#[InheritanceJoinedTable(outerKey: 'index_id')]
class LocalSupplier extends Supplier {}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures16;

use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Inheritance\JoinedTable as InheritanceJoinedTable;

/**
 * @Entity(table="external_suppliers")
 * @InheritanceJoinedTable(outerKey="custom_id")
 */
#[Entity(table: 'external_suppliers')]
#[InheritanceJoinedTable(outerKey: 'custom_id')]
class ExternalSupplier extends Supplier {}

<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures10;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Table;
use Arcano\Annotated\Annotation\Table\Index;

/**
 * @Entity(role="MergedMeta")
 * @Table(
 *      columns = {
 *          "name": @Column(type="string"),
 *      }
 * )
 */
#[Index(columns: ['name', 'id DESC'])]
class MergedMetadata
{
    #[Column(type: 'primary')]
    protected $id;
}

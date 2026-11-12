<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures9;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Table;
use Arcano\Annotated\Annotation\Table\Index;

/**
 * @Entity()
 * @Table(
 *      columns = {
 *          "name": @Column(type="string"),
 *          @Column(property="other_name", type="string"),
 *      },
 *      indexes = {
 *          @Index(columns={"name", "id DESC"}),
 *      }
 * )
 */
#[Entity]
#[Table(
    columns: [
        'name' => new Column(type: 'string'),
        new Column(type: 'string', property: 'other_name'),
    ],
    indexes: [
        new Index(columns: ['name', 'id DESC']),
    ],
)]
class OrderedIdx
{
    /** @Column(type="primary") */
    #[Column(type: 'primary')]
    protected $id;
}

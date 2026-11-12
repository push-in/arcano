<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures11;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Table;

/**
 * @Entity()
 * @Table(
 *      columns = {
 *          "name1": @Column(property="name2", type="string")
 *      }
 * )
 */
class BadEntity
{
    /** @Column(type="primary") */
    protected $id;
}

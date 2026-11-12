<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures12;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Table;
use Arcano\Annotated\Annotation\Table\PrimaryKey;

/**
 * @Entity(role="compositePost")
 * @Table(
 *      columns = {
 *          "id": @Column(type="primary"),
 *          "userId": @Column(type="integer", name="user_id"),
 *          "title": @Column(type="string"),
 *      },
 *      primary = @PrimaryKey(columns={"id", "user_id"}),
 * )
 */
#[Entity(role: 'compositePost')]
#[Column(name: 'id', type: 'primary')]
#[Column(property: 'userId', type: 'integer', name: 'user_id')]
#[PrimaryKey(columns: ['id', 'user_id'])]
class CompositePost
{
    protected $id;
    protected $userId;
    protected $title;
}

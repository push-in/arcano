<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Arcano\Schema\Generator\Migrations\Tests\Functional\Fixtures\Init;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Table\Index;

#[Entity]
#[Index(columns: ['email'], unique: true)]
class User
{
    #[Column(type: 'primary')]
    protected int $id;

    #[Column(type: 'string')]
    protected $email;
}

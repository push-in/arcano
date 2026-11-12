<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Arcano\Schema\Generator\Migrations\Tests\Functional\Fixtures\Alter;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;

#[Entity]
class User
{
    #[Column(type: 'primary')]
    protected int $id;
}

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

#[Entity(database: 'secondary')]
class Other
{
    #[Column(type: 'primary')]
    protected int $id;

    #[Column(type: 'enum(active,disabled)', default: 'active')]
    protected string $status;
}

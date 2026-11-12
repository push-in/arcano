<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures27;

use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\Inheritance\SingleTable;

#[Entity]
#[SingleTable]
class SoSti extends SoParent {}

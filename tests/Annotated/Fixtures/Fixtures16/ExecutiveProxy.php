<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures16;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;

/**
 * This proxy class doesn't have an {@see Entity} annotation (attribute) declaration,
 * and it shouldn't be presented in Schema.
 * But all the classes that extend this class should contain all the fields from this class.
 */
class ExecutiveProxy extends Employee
{
    /** @Column(type="string", name="proxy") */
    #[Column(type: 'string', name: 'proxy')]
    public ?string $proxyFieldWithAnnotation = null;

    protected int $proxyField;
}

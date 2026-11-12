<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures25;

use Arcano\Annotated\Annotation\Column;
use Arcano\Annotated\Annotation\Entity;
use Arcano\Annotated\Annotation\GeneratedValue;

/**
 * @Entity(role="withGeneratedFields", table="with_generated_fields")
 */
#[Entity(role: 'withGeneratedFields', table: 'with_generated_fields')]
class WithGeneratedFields
{
    /**
     * @Column(type="primary")
     */
    #[Column(type: 'primary')]
    public int $id;

    /**
     * @Column(type="datetime", name="created_at")
     * @GeneratedValue(beforeInsert=true)
     */
    #[Column(type: 'datetime', name: 'created_at'),
        GeneratedValue(beforeInsert: true)]
    public \DateTimeImmutable $createdAt;

    /**
     * @Column(type="datetime", name="created_at_generated_by_database")
     * @GeneratedValue(onInsert=true)
     */
    #[Column(type: 'datetime', name: 'created_at_generated_by_database'),
        GeneratedValue(onInsert: true)]
    public \DateTimeImmutable $createdAtGeneratedByDatabase;

    /**
     * @Column(type="datetime", name="created_at")
     * @GeneratedValue(beforeInsert=true, beforeUpdate=true)
     */
    #[Column(type: 'datetime', name: 'updated_at'),
        GeneratedValue(beforeInsert: true, beforeUpdate: true)]
    public \DateTimeImmutable $updatedAt;
}

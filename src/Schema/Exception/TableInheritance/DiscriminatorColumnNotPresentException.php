<?php

declare(strict_types=1);

namespace Arcano\Schema\Exception\TableInheritance;

use Arcano\Schema\Definition\Entity;
use Arcano\Schema\Exception\TableInheritanceException;
use Yiisoft\FriendlyException\FriendlyExceptionInterface;

class DiscriminatorColumnNotPresentException extends TableInheritanceException implements FriendlyExceptionInterface
{
    public function __construct(private Entity $entity)
    {
        parent::__construct(sprintf(
            'Discriminator column for the `%s` role should be defined.',
            (string) ($this->entity->getRole() ?? $this->entity->getClass()),
        ));
    }

    public function getName(): string
    {
        return 'Discriminator column is not present.';
    }

    public function getSolution(): ?string
    {
        $fields = implode('`, `', $this->entity->getFields()->getNames());

        return sprintf(
            "Discriminator column is required for Single Table Inheritance schema.\n" .
            'You have to specify one of the defined fields of the `%s` role: `%s`',
            (string) ($this->entity->getRole() ?? $this->entity->getClass()),
            $fields,
        );
    }
}

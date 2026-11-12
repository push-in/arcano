<?php

declare(strict_types=1);

namespace Arcano\Annotated\Tests\Fixtures\Fixtures17;

use Arcano\ORM\SchemaInterface;
use Arcano\Schema\Registry;
use Arcano\Schema\SchemaModifierInterface;

/**
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *      @Attribute("class", type="string", required=true)
 * })
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class MapperSegmentSchemaModifier implements SchemaModifierInterface
{
    private string $role;
    private string $class;

    public function __construct(array $values)
    {
        foreach ($values as $key => $value) {
            $this->$key = $value;
        }
    }

    public function withRole(string $role): static
    {
        $this->role = $role;
        return $this;
    }

    public function compute(Registry $registry): void
    {
        $registry->getEntity($this->role)->setTypecast('test-typecast');
    }

    public function render(Registry $registry): void {}

    public function modifySchema(array &$schema): void
    {
        $schema[SchemaInterface::MAPPER] = $this->class;
    }
}

<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Generator;

use Arcano\Database\DatabaseProviderInterface;
use Arcano\Schema\Compiler;
use Arcano\Schema\Definition\Entity;
use Arcano\Schema\Definition\Field;
use Arcano\Schema\Exception\SchemaException;
use Arcano\Schema\Generator\RenderModifiers;
use Arcano\Schema\Registry;
use Arcano\Schema\SchemaModifierInterface;
use Arcano\Schema\Tests\Fixtures\BrokenSchemaModifier;
use Arcano\Schema\Tests\Fixtures\User;
use PHPUnit\Framework\TestCase;

class RenderModifiersTest extends TestCase
{
    public function testEntityShouldBeRendered()
    {
        $r = new Registry(
            $this->createMock(DatabaseProviderInterface::class),
        );

        $user = new Entity();
        $user->setRole('user')->setClass(User::class);
        $user->getFields()->set('foo_bar', (new Field())->setType('primary')->setColumn('id'));

        $mock = $this->createMock(SchemaModifierInterface::class);
        $mock->expects(self::once())->method('withRole')->with('user')->willReturnSelf();

        $user->addSchemaModifier($mock);

        $mock
            ->expects($this->atLeastOnce())
            ->method('render')
            ->with($this->equalTo($r));

        $r->register($user);

        $c = new Compiler();
        $c->compile($r, [new RenderModifiers()]);
    }

    /**
     * @dataProvider brokenMethodsDataProvider
     */
    public function testErrorInsideModifierShouldThrowAnException(string $method)
    {
        $this->expectException(SchemaException::class);
        $this->expectExceptionMessage(
            'Unable to render modifier `Arcano\Schema\Tests\Fixtures\BrokenSchemaModifier` for the `user` role.',
        );

        $r = new Registry(
            $this->createMock(DatabaseProviderInterface::class),
        );

        $user = new Entity();
        $user->setRole('user')->setClass(User::class);
        $user->addSchemaModifier(new BrokenSchemaModifier(BrokenSchemaModifier::class . '::' . $method));

        $r->register($user);

        $c = new Compiler();
        $c->compile($r, [new RenderModifiers()]);
    }

    public function brokenMethodsDataProvider()
    {
        return [
            // 'withRole' => ['withRole'],
            'render' => ['render'],
        ];
    }
}

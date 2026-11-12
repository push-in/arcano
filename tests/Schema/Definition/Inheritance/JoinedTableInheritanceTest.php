<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Definition\Inheritance;

use Arcano\Database\DatabaseProviderInterface;
use Arcano\ORM\SchemaInterface;
use Arcano\Schema\Compiler;
use Arcano\Schema\Definition\Entity;
use Arcano\Schema\Definition\Field;
use Arcano\Schema\Definition\Inheritance\JoinedTable;
use Arcano\Schema\Exception\TableInheritance\WrongParentKeyColumnException;
use Arcano\Schema\Registry;
use Arcano\Schema\Tests\Fixtures\Author;
use Arcano\Schema\Tests\Fixtures\User;
use PHPUnit\Framework\TestCase;

class JoinedTableInheritanceTest extends TestCase
{
    public function testJoinedTableShouldBeAddedToSchema()
    {
        $r = new Registry(
            $this->createMock(DatabaseProviderInterface::class),
        );

        $user = new Entity();
        $user->setRole('user')->setClass(User::class);
        $user->getFields()->set('foo_bar', (new Field())->setType('primary')->setColumn('id'));

        $author = new Entity();
        $author->setRole('author')->setClass(Author::class);
        $author->setInheritance($inheritance = new JoinedTable($user, 'foo_bar'));
        $author->getFields()->set('id', (new Field())->setType('primary')->setColumn('id'));

        $r->register($user);
        $r->register($author);

        $schema = (new Compiler())->compile($r);

        $this->assertSame('user', $schema['author'][SchemaInterface::PARENT]);
        $this->assertSame('foo_bar', $schema['author'][SchemaInterface::PARENT_KEY] ?? null);
    }

    public function testJoinedTableWithoutOuterKeyShouldBeAddedToSchema()
    {
        $r = new Registry(
            $this->createMock(DatabaseProviderInterface::class),
        );

        $user = new Entity();
        $user->setRole('user')->setClass(User::class);
        $user->getFields()->set('id', (new Field())->setType('primary')->setColumn('id'));

        $author = new Entity();
        $author->setRole('author')->setClass(Author::class);
        $author->setInheritance($inheritance = new JoinedTable($user));
        $author->getFields()->set('id', (new Field())->setType('primary')->setColumn('id'));

        $r->register($user);
        $r->register($author);

        $schema = (new Compiler())->compile($r);

        $this->assertSame('user', $schema['author'][SchemaInterface::PARENT]);
        $this->assertEmpty($schema['author'][SchemaInterface::PARENT_KEY] ?? null);
    }

    public function testJoinedTableWithNonExistsOuterKeyShouldThrowAnException()
    {
        $this->expectException(WrongParentKeyColumnException::class);
        $this->expectExceptionMessage('Outer key column `foo_bar` is not found among fields of the `user` role.');

        $r = new Registry(
            $this->createMock(DatabaseProviderInterface::class),
        );

        $user = new Entity();
        $user->setRole('user')->setClass(User::class);
        $user->getFields()->set('id', (new Field())->setType('primary')->setColumn('id'));

        $author = new Entity();
        $author->setRole('author')->setClass(Author::class);
        $author->setInheritance($inheritance = new JoinedTable($user, 'foo_bar'));
        $author->getFields()->set('id', (new Field())->setType('primary')->setColumn('id'));

        $r->register($user);
        $r->register($author);

        (new Compiler())->compile($r);
    }
}

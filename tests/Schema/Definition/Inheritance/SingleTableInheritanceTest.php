<?php

declare(strict_types=1);

namespace Arcano\Schema\Tests\Definition\Inheritance;

use Arcano\Database\DatabaseProviderInterface;
use Arcano\ORM\SchemaInterface;
use Arcano\Schema\Compiler;
use Arcano\Schema\Definition\Entity;
use Arcano\Schema\Definition\Field;
use Arcano\Schema\Definition\Inheritance\SingleTable;
use Arcano\Schema\Exception\TableInheritance\DiscriminatorColumnNotPresentException;
use Arcano\Schema\Exception\TableInheritance\WrongDiscriminatorColumnException;
use Arcano\Schema\Registry;
use Arcano\Schema\Tests\Fixtures\Author;
use Arcano\Schema\Tests\Fixtures\User;
use PHPUnit\Framework\TestCase;

class SingleTableInheritanceTest extends TestCase
{
    public function testSingleTableShouldBeAddedToSchema()
    {
        $r = new Registry(
            $this->createMock(DatabaseProviderInterface::class),
        );

        $author = new Entity();
        $author->setRole('author')->setClass(Author::class);
        $author->markAsChildOfSingleTableInheritance(User::class);

        $user = new Entity();
        $user->setRole('user')->setClass(User::class);
        $user->setInheritance($inheritance = new SingleTable());

        $inheritance->setDiscriminator('type');
        $inheritance->addChild('foo', 'bar');
        $inheritance->addChild('author', 'author');

        $user->getFields()->set('id', (new Field())->setType('primary')->setColumn('id'));
        $user->getFields()->set('type', (new Field())->setType('string')->setColumn('type'));

        $r->register($user);
        $r->register($author);

        $schema = (new Compiler())->compile($r);

        $this->assertSame(['foo' => 'bar', 'author' => 'author'], $schema['user'][SchemaInterface::CHILDREN]);
        $this->assertSame('type', $schema['user'][SchemaInterface::DISCRIMINATOR]);

        $this->assertSame([SchemaInterface::ENTITY => Author::class], $schema['author']);
    }

    public function testSingleTableWithExplicitPkShouldBeAddedToSchema()
    {
        $r = new Registry(
            $this->createMock(DatabaseProviderInterface::class),
        );

        $author = new Entity();
        $author->setRole('author')->setClass(Author::class);
        $author->getFields()->set('id', (new Field())->setType('primary')->setColumn('id'));
        $author->markAsChildOfSingleTableInheritance(User::class);

        $user = new Entity();
        $user->setRole('user')->setClass(User::class);
        $user->setInheritance($inheritance = new SingleTable());

        $inheritance->setDiscriminator('type');
        $inheritance->addChild('foo', 'bar');
        $inheritance->addChild('author', 'author');

        $user->getFields()->set('id', (new Field())->setType('primary')->setColumn('id'));
        $user->getFields()->set('type', (new Field())->setType('string')->setColumn('type'));

        $r->register($user);
        $r->register($author);

        $schema = (new Compiler())->compile($r);

        $this->assertSame(['foo' => 'bar', 'author' => 'author'], $schema['user'][SchemaInterface::CHILDREN]);
        $this->assertSame('type', $schema['user'][SchemaInterface::DISCRIMINATOR]);

        $this->assertSame([SchemaInterface::ENTITY => Author::class], $schema['author']);
    }

    public function testSingleTableWithoutDiscriminatorColumnShouldThrowAnException()
    {
        $this->expectException(DiscriminatorColumnNotPresentException::class);
        $this->expectExceptionMessage('Discriminator column for the `user` role should be defined.');

        $r = new Registry(
            $this->createMock(DatabaseProviderInterface::class),
        );

        $user = new Entity();
        $user->setRole('user')->setClass(User::class);
        $user->setInheritance(new SingleTable());

        $user->getFields()->set('id', (new Field())->setType('primary')->setColumn('id'));

        $r->register($user);

        (new Compiler())->compile($r);
    }

    public function testSingleTableWithNonExistsDiscriminatorColumnShouldThrowAnException()
    {
        $this->expectException(WrongDiscriminatorColumnException::class);
        $this->expectExceptionMessage('Discriminator column `type` is not found among fields of the `user` role.');

        $r = new Registry(
            $this->createMock(DatabaseProviderInterface::class),
        );

        $user = new Entity();
        $user->setRole('user')->setClass(User::class);
        $user->setInheritance($inheritance = new SingleTable());
        $inheritance->setDiscriminator('type');
        $user->getFields()->set(
            'id',
            (new Field())->setType('primary')->setColumn('id'),
        );

        $r->register($user);

        (new Compiler())->compile($r);
    }
}

<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Unit\Stub;

use Arcano\Database\Config\DriverConfig;
use Arcano\Database\Driver\Driver;
use Arcano\Database\Driver\HandlerInterface;
use Arcano\Database\Driver\PDOInterface;
use Arcano\Database\Driver\SQLite\SQLiteCompiler;
use Arcano\Database\Driver\SQLite\SQLiteHandler;
use Arcano\Database\Exception\StatementException;
use Arcano\Database\Query\BuilderInterface;
use Arcano\Database\Query\QueryBuilder;

class TestDriver extends Driver
{
    protected ?PDOInterface $pdoMock = null;

    public static function create(DriverConfig $config): Driver
    {
        return new self(
            $config,
            new SQLiteHandler(),
            new SQLiteCompiler('""'),
            QueryBuilder::defaultBuilder(),
        );
    }

    public static function createWith(
        DriverConfig $config,
        HandlerInterface $handler,
        BuilderInterface $builder,
        ?PDOInterface $pdoMock = null,
    ): Driver {
        $driver = new self(
            $config,
            $handler,
            new SQLiteCompiler('""'),
            $builder,
        );

        $driver->pdoMock = $pdoMock;

        return $driver;
    }

    public function getType(): string
    {
        return 'test';
    }

    protected function mapException(\Throwable $exception, string $query): StatementException
    {
        throw $exception;
    }

    protected function getPDO(): \PDO|PDOInterface
    {
        return $this->pdoMock ?? parent::getPDO();
    }
}

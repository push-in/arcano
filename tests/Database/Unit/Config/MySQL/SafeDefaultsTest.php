<?php

declare(strict_types=1);

namespace Arcano\Database\Tests\Unit\Config\MySQL;

use Arcano\Database\Config\MySQL\DsnConnectionConfig;
use Arcano\Database\Config\MySQL\SocketConnectionConfig;
use Arcano\Database\Config\MySQL\TcpConnectionConfig;
use PHPUnit\Framework\TestCase;

final class SafeDefaultsTest extends TestCase
{
    public function testUsesServerPreparedStatementsAndUtf8mb4(): void
    {
        foreach ([
            new TcpConnectionConfig('app'),
            new SocketConnectionConfig('app', '/run/mysqld.sock'),
            new DsnConnectionConfig('mysql:host=db;dbname=app'),
        ] as $config) {
            $options = $config->getOptions();
            // Real prepares: values never go through client-side escaping.
            self::assertFalse($options[\PDO::ATTR_EMULATE_PREPARES]);
            // utf8 is the 3-byte utf8mb3, which rejects emoji and other 4-byte characters.
            self::assertSame('SET NAMES utf8mb4', $options[1002]);
        }
        self::assertStringEndsWith(';charset=utf8mb4', (new TcpConnectionConfig('app'))->getDsn());
        self::assertStringEndsWith(';charset=utf8mb4', (new SocketConnectionConfig('app', '/run/mysqld.sock'))->getDsn());
    }

    public function testOptionsStillOverrideTheDefaults(): void
    {
        $config = new TcpConnectionConfig('app', options: [\PDO::ATTR_EMULATE_PREPARES => true]);
        self::assertTrue($config->getOptions()[\PDO::ATTR_EMULATE_PREPARES]);
    }
}

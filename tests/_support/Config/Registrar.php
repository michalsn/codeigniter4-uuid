<?php

declare(strict_types=1);

namespace Tests\Support\Config;

/**
 * Class Registrar
 *
 * Provides a basic registrar class for testing BaseConfig registration functions.
 */
class Registrar
{
    /**
     * DB config array for testing purposes.
     *
     * @var array<string, array<string, array<string, bool|int|string>|bool|int|string>>
     */
    protected static array $dbConfig = [
        'MySQLi' => [
            'DSN'      => '',
            'hostname' => '127.0.0.1',
            'username' => 'root',
            'password' => '',
            'database' => 'test',
            'DBDriver' => 'MySQLi',
            'DBPrefix' => 'db_',
            'pConnect' => false,
            'DBDebug'  => true,
            'charset'  => 'utf8mb4',
            'DBCollat' => 'utf8mb4_general_ci',
            'swapPre'  => '',
            'encrypt'  => false,
            'compress' => false,
            'strictOn' => true,
            'failover' => [],
            'port'     => 3306,
        ],
    ];

    /**
     * Override database config
     *
     * @return array<string, array<string, bool|int|string>|bool|int|string>
     */
    public static function Database(): array
    {
        $config = [];

        // Under GitHub Actions, we can set an ENV var named 'DB'
        // so that we can test against multiple databases.
        if (($group = getenv('DB')) && isset(self::$dbConfig[$group])) {
            $config['tests'] = self::$dbConfig[$group];
        }

        return $config;
    }
}

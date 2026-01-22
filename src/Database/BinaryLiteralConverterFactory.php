<?php

declare(strict_types=1);

namespace Michalsn\CodeIgniterUuid\Database;

use Michalsn\CodeIgniterUuid\Exceptions\CodeIgniterUuidException;

class BinaryLiteralConverterFactory
{
    /**
     * Cached converter instances.
     *
     * @var array<string, BinaryLiteralConverterInterface>
     */
    private static array $converters = [];

    /**
     * Get the appropriate binary literal converter for the given database driver.
     *
     * @throws CodeIgniterUuidException
     */
    public static function get(string $driver): BinaryLiteralConverterInterface
    {
        if (isset(self::$converters[$driver])) {
            return self::$converters[$driver];
        }

        self::$converters[$driver] = match ($driver) {
            'MySQLi', 'SQLSRV' => new MySqliBinaryConverter(),
            'Postgre' => new PostgreBinaryConverter(),
            'SQLite3' => new SQLite3BinaryConverter(),
            'OCI8'    => new OCI8BinaryConverter(),
            default   => throw CodeIgniterUuidException::forUnknownDbDriver(),
        };

        return self::$converters[$driver];
    }
}

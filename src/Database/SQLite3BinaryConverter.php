<?php

declare(strict_types=1);

namespace Michalsn\CodeIgniterUuid\Database;

use CodeIgniter\Database\RawSql;
use RuntimeException;

/**
 * Binary literal converter for SQLite3 driver.
 */
class SQLite3BinaryConverter implements BinaryLiteralConverterInterface
{
    public function toBinaryLiteral(string $binary): RawSql
    {
        $hex = bin2hex($binary);

        return new RawSql("X'{$hex}'");
    }

    public function fromBinaryLiteral(RawSql $literal): string
    {
        $literal = (string) $literal;

        // Format: X'DEADBEEF'
        if (preg_match("/^X'([0-9a-fA-F]+)'$/", $literal, $m)) {
            return hex2bin($m[1]);
        }

        throw new RuntimeException('Invalid binary literal format for SQLite3 driver');
    }
}

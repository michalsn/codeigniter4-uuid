<?php

declare(strict_types=1);

namespace Michalsn\CodeIgniterUuid\Database;

use CodeIgniter\Database\RawSql;
use RuntimeException;

/**
 * Binary literal converter for MySQLi and SQLSRV drivers.
 */
class MySqliBinaryConverter implements BinaryLiteralConverterInterface
{
    public function toBinaryLiteral(string $binary): RawSql
    {
        $hex = bin2hex($binary);

        return new RawSql("0x{$hex}");
    }

    public function fromBinaryLiteral(RawSql $literal): string
    {
        $literal = (string) $literal;

        // Format: 0xDEADBEEF
        if (preg_match('/^0x([0-9a-fA-F]+)$/', $literal, $m)) {
            return hex2bin($m[1]);
        }

        throw new RuntimeException('Invalid binary literal format for MySQLi/SQLSRV driver');
    }
}

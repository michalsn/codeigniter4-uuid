<?php

declare(strict_types=1);

namespace Michalsn\CodeIgniterUuid\Database;

use CodeIgniter\Database\RawSql;
use RuntimeException;

/**
 * Binary literal converter for PostgreSQL driver.
 */
class PostgreBinaryConverter implements BinaryLiteralConverterInterface
{
    public function toBinaryLiteral(string $binary): RawSql
    {
        $hex = bin2hex($binary);

        return new RawSql("'\\\\x{$hex}'");
    }

    public function fromBinaryLiteral(RawSql $literal): string
    {
        $literal = trim((string) $literal);

        // PostgreSQL BYTEA hex format: '\\xDEADBEEF' (SQL literal form)
        // Actual stored value from SELECT is usually: "\xDEADBEEF"
        if (preg_match('/^\\\\?x([0-9a-fA-F]+)$/', $literal, $m)) {
            return hex2bin($m[1]);
        }

        // Or full SQL literal: '\\xDEADBEEF'
        if (preg_match("/^'\\\\\\\\x([0-9a-fA-F]+)'$/", $literal, $m)) {
            return hex2bin($m[1]);
        }

        throw new RuntimeException('Invalid binary literal format for PostgreSQL driver');
    }
}

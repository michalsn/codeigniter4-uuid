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

        return new RawSql("decode('{$hex}', 'hex')");
    }

    public function fromBinaryLiteral(RawSql $literal): string
    {
        $literal = (string) $literal;

        if (preg_match("/^decode\\('([0-9a-fA-F]+)',\\s*'hex'\\)$/", $literal, $m)) {
            return hex2bin($m[1]);
        }

        throw new RuntimeException('Invalid binary literal format for PostgreSQL driver');
    }
}

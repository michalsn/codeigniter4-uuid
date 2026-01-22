<?php

declare(strict_types=1);

namespace Michalsn\CodeIgniterUuid\Database;

use CodeIgniter\Database\RawSql;
use RuntimeException;

/**
 * Binary literal converter for Oracle (OCI8) driver.
 */
class OCI8BinaryConverter implements BinaryLiteralConverterInterface
{
    public function toBinaryLiteral(string $binary): RawSql
    {
        $hex = bin2hex($binary);

        return new RawSql("HEXTORAW('{$hex}')");
    }

    public function fromBinaryLiteral(RawSql $literal): string
    {
        $literal = (string) $literal;

        // Format: HEXTORAW('DEADBEEF')
        if (preg_match("/^HEXTORAW\\('([0-9a-fA-F]+)'\\)$/i", $literal, $m)) {
            return hex2bin($m[1]);
        }

        throw new RuntimeException('Invalid binary literal format for OCI8 driver');
    }
}

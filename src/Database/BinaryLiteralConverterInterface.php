<?php

declare(strict_types=1);

namespace Michalsn\CodeIgniterUuid\Database;

use CodeIgniter\Database\RawSql;

interface BinaryLiteralConverterInterface
{
    /**
     * Convert raw binary to a database-specific binary literal for SQL.
     */
    public function toBinaryLiteral(string $binary): RawSql;

    /**
     * Convert a database-specific binary literal back to raw binary.
     */
    public function fromBinaryLiteral(RawSql $literal): string;
}

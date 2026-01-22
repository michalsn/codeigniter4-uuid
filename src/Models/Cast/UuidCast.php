<?php

declare(strict_types=1);

namespace Michalsn\CodeIgniterUuid\Models\Cast;

use CodeIgniter\Database\RawSql;
use CodeIgniter\DataCaster\Cast\BaseCast;
use Michalsn\CodeIgniterUuid\Database\BinaryLiteralConverterFactory;
use Michalsn\CodeIgniterUuid\Enums\UuidType;
use Symfony\Component\Uid\Uuid;

class UuidCast extends BaseCast
{
    public static function get(
        mixed $value,
        array $params = [],
        ?object $helper = null,
    ): ?string {
        $type = $params[1] ?? config('Uuid')->defaultType->value;

        if ($type === UuidType::BYTES->value) {
            if ($value === null) {
                return $value;
            }

            // PostgreSQL returns BYTEA in hex format: \xDEADBEEF...
            if (is_string($value) && str_starts_with($value, '\\x')) {
                $value = hex2bin(substr($value, 2));
            }

            return Uuid::fromBinary($value)->toRfc4122();
        }

        return $value;
    }

    public static function set(
        mixed $value,
        array $params = [],
        ?object $helper = null,
    ): RawSql|string|null {
        $type = $params[1] ?? config('Uuid')->defaultType->value;

        if ($type === UuidType::BYTES->value) {
            if ($value === null) {
                return $value;
            }

            $binary = Uuid::fromString($value)->toBinary();

            // Use database-specific binary literal format
            if ($helper !== null && isset($helper->DBDriver)) {
                return BinaryLiteralConverterFactory::get($helper->DBDriver)->toBinaryLiteral($binary);
            }

            return $binary;
        }

        return $value;
    }
}

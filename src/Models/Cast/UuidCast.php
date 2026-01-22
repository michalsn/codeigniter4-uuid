<?php

declare(strict_types=1);

namespace Michalsn\CodeIgniterUuid\Models\Cast;

use CodeIgniter\DataCaster\Cast\BaseCast;
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

            return Uuid::fromBinary($value)->toRfc4122();
        }

        return $value;
    }

    public static function set(
        mixed $value,
        array $params = [],
        ?object $helper = null,
    ): ?string {
        $type = $params[1] ?? config('Uuid')->defaultType->value;

        if ($type === UuidType::BYTES->value) {
            if ($value === null) {
                return $value;
            }

            return Uuid::fromString($value)->toBinary();
        }

        return $value;
    }
}

<?php

declare(strict_types=1);

namespace Michalsn\CodeIgniterUuid;

use Michalsn\CodeIgniterUuid\Config\Uuid as UuidConfig;
use Michalsn\CodeIgniterUuid\Enums\UuidVersion;
use Michalsn\CodeIgniterUuid\Exceptions\CodeIgniterUuidException;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Uid\Uuid as SymfonyUuid;

class Uuid
{
    public function __construct(private ?UuidConfig $config = null)
    {
        $this->config = $config ?? config('Uuid');
    }

    public function fromString(string $uuid): UuidWrapper
    {
        // Check if it's a ULID format (26 chars, no hyphens, Crockford base32)
        if (Ulid::isValid($uuid)) {
            return new UuidWrapper(Ulid::fromString($uuid));
        }

        // Handle UUID without hyphens (32 hex chars)
        if (strlen($uuid) === 32 && ctype_xdigit($uuid)) {
            $uuid = sprintf(
                '%s-%s-%s-%s-%s',
                substr($uuid, 0, 8),
                substr($uuid, 8, 4),
                substr($uuid, 12, 4),
                substr($uuid, 16, 4),
                substr($uuid, 20, 12),
            );
        }

        return new UuidWrapper(SymfonyUuid::fromString($uuid));
    }

    public function fromValue(string $value): UuidWrapper
    {
        if (ctype_print($value)) {
            return $this->fromString($value);
        }

        return new UuidWrapper(SymfonyUuid::fromBinary($value));
    }

    public function isValid(string $value): bool
    {
        // Check if it's a valid ULID
        if (Ulid::isValid($value)) {
            return true;
        }

        // Handle UUID without hyphens (32 hex chars)
        if (strlen($value) === 32 && ctype_xdigit($value)) {
            $value = sprintf(
                '%s-%s-%s-%s-%s',
                substr($value, 0, 8),
                substr($value, 8, 4),
                substr($value, 12, 4),
                substr($value, 16, 4),
                substr($value, 20, 12),
            );
        }

        return SymfonyUuid::isValid($value);
    }

    public function generate(string|UuidVersion|null $version = null): UuidWrapper
    {
        if ($version === null) {
            $version = $this->config->defaultVersion;
        } elseif (is_string($version)) {
            $version = UuidVersion::tryFrom($version)
                ?? throw CodeIgniterUuidException::forUnsupportedUuidVersion($version);
        }

        return match ($version) {
            UuidVersion::V1   => $this->uuid1(),
            UuidVersion::V3   => $this->uuid3(),
            UuidVersion::V4   => $this->uuid4(),
            UuidVersion::V5   => $this->uuid5(),
            UuidVersion::V6   => $this->uuid6(),
            UuidVersion::V7   => $this->uuid7(),
            UuidVersion::ULID => $this->ulid(),
        };
    }

    public function uuid1(): UuidWrapper
    {
        return new UuidWrapper(SymfonyUuid::v1());
    }

    public function uuid3(): UuidWrapper
    {
        $namespace = SymfonyUuid::fromString($this->config->v3['ns']);

        return new UuidWrapper(SymfonyUuid::v3($namespace, $this->config->v3['name'] ?? ''));
    }

    public function uuid4(): UuidWrapper
    {
        return new UuidWrapper(SymfonyUuid::v4());
    }

    public function uuid5(): UuidWrapper
    {
        $namespace = SymfonyUuid::fromString($this->config->v5['ns']);

        return new UuidWrapper(SymfonyUuid::v5($namespace, $this->config->v5['name'] ?? ''));
    }

    public function uuid6(): UuidWrapper
    {
        return new UuidWrapper(SymfonyUuid::v6());
    }

    public function uuid7(): UuidWrapper
    {
        return new UuidWrapper(SymfonyUuid::v7());
    }

    public function ulid(): UuidWrapper
    {
        return new UuidWrapper(new Ulid());
    }
}

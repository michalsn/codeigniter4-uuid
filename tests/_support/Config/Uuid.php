<?php

declare(strict_types=1);

namespace Tests\Support\Config;

use Michalsn\CodeIgniterUuid\Config\Uuid as UuidConfig;
use Michalsn\CodeIgniterUuid\Enums\UuidType;
use Michalsn\CodeIgniterUuid\Enums\UuidVersion;
use Symfony\Component\Uid\Uuid as SymfonyUuid;

class Uuid extends UuidConfig
{
    public UuidVersion $defaultVersion = UuidVersion::V7;
    public UuidType $defaultType       = UuidType::STRING;
    public array $v1                   = [
        'node' => null,
    ];
    public array $v3 = [
        'ns'   => SymfonyUuid::NAMESPACE_URL,
        'name' => 'https://example.com/',
    ];
    public array $v5 = [
        'ns'   => SymfonyUuid::NAMESPACE_URL,
        'name' => 'https://example.com/',
    ];
    public array $v6 = [
        'node' => null,
    ];
    public array $v7 = [
        'dateTime' => null,
    ];
    public array $ulid = [
        'dateTime' => null,
    ];
}

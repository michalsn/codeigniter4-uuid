<?php

declare(strict_types=1);

namespace Michalsn\CodeIgniterUuid\Enums;

enum UuidVersion: string
{
    case V1   = 'v1';
    case V3   = 'v3';
    case V4   = 'v4';
    case V5   = 'v5';
    case V6   = 'v6';
    case V7   = 'v7';
    case ULID = 'ulid';
}

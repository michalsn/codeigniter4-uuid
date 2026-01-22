<?php

declare(strict_types=1);

namespace Michalsn\CodeIgniterUuid\Enums;

enum UuidType: string
{
    case STRING = 'string';
    case BYTES  = 'bytes';
}

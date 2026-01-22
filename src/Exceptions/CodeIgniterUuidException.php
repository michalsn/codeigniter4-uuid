<?php

declare(strict_types=1);

namespace Michalsn\CodeIgniterUuid\Exceptions;

use RuntimeException;

class CodeIgniterUuidException extends RuntimeException
{
    public static function forUnsupportedUuidVersion(string $version)
    {
        return new self(lang('CodeIgniterUuid.unsupportedUuidVersion', [$version]));
    }

    public static function forIncorrectUseAutoIncrementValue()
    {
        return new self(lang('CodeIgniterUuid.incorrectUseAutoIncrementValue'));
    }

    public static function forUnknownDbDriver()
    {
        return new self(lang('CodeIgniterUuid.unknownDbDriver'));
    }
}

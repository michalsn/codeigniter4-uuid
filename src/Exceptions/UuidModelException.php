<?php namespace Michalsn\Uuid\Exceptions;

class UuidModelException extends \RuntimeException
{
    public static function forIncorrectUuidVersion(string $version)
    {
        return new self(lang('UuidModel.incorrectUuidVersion', [$version]));
    }

    public static function forIncorrectValueInUuidFields(string $primaryKey)
    {
        return new self(lang('UuidModel.incorrectValueInUuidFields', [$primaryKey]));
    }
}
<?php namespace Michalsn\UuidModel\Exceptions;

class UuidModelException extends \RuntimeException
{
    public static function forIncorrectUuidVersion(string $version)
    {
        return new self(lang('UuidModel.incorrectUuidVersion', [$version]));
    }
}
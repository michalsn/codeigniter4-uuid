<?php namespace Michalsn\UuidModel\Config;

use CodeIgniter\Config\BaseService;
use Michalsn\UuidModel\Uuid;

class Services extends BaseService
{
    public static function uuid(bool $getShared = true)
    {
		if ($getShared)
		{
			return static::getSharedInstance('uuid');
		}

		$config = config('Uuid');

		return new Uuid($config);
	}
}
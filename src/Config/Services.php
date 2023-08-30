<?php namespace Michalsn\Uuid\Config;

use CodeIgniter\Config\BaseService;
use Michalsn\Uuid\Uuid;

class Services extends BaseService
{
    /**
     * @return Uuid
     */
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
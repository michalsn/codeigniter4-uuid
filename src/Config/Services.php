<?php namespace Michalsn\Uuid\Config;

use CodeIgniter\Config\BaseService;
use Michalsn\Uuid\Uuid;

class Services extends BaseService
{
    /**
     * Generates and returns a new Uuid instance either as a shared instance or a new instance.
     *
     * @param bool $getShared Determines whether to return a shared instance of Uuid. Defaults to true.
     * @return Uuid Returns an instance of the Uuid class.
     */
    public static function uuid(bool $getShared = true) : Uuid
    {
		if ($getShared)
		{
			return static::getSharedInstance('uuid');
		}

		$config = config('Uuid');

		return new Uuid($config);
	}
}
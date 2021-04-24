<?php

namespace Michalsn\Uuid;

use CodeIgniter\Entity\Cast\BaseCast;

class UuidCast extends BaseCast
{
	/**
	 * Get
	 *
	 * @param mixed $value  Data
	 * @param array $params Additional param
	 *
	 * @return mixed
	 */
	public static function get($value, array $params = [])
	{
		if (! ctype_print($value))
		{
			$value = service('uuid')->fromBytes($value)->toString();
		}

		return $value;
	}

	/**
	 * Set
	 *
	 * @param mixed $value  Data
	 * @param array $params Additional param
	 *
	 * @return mixed
	 */
	public static function set($value, array $params = [])
	{
		if (ctype_print($value))
		{
			$value = service('uuid')->fromString($value)->getBytes();
		}

		return $value;
	}
}
<?php

namespace Michalsn\Uuid;

use CodeIgniter\Entity\Entity;

/**
 * Entity encapsulation, for use with Michalsn\Uuid\UuidModel
 */
class UuidEntity extends Entity
{
	/**
	 * Array of UUID fields that are stored in byte format.
	 *
	 * @param array
	 */
	protected $uuids = ['id'];

	/**
	 * Ensures our "original" values match the current values.
	 *
	 * @return $this
	 */
	public function syncOriginal()
	{
		$this->original = $this->attributes;

		if (! empty($this->uuids) && ! empty($this->attributes))
		{
			// Load Uuid service
			$uuidObj = service('uuid');

			// Loop through the UUID array fields
			foreach ($this->uuids as $uuid)
			{
				// Check if field is in byte format
				if (isset($this->attributes[$uuid]) && ! ctype_print($this->attributes[$uuid]))
				{
					$this->original[$uuid] = $uuidObj->fromBytes($this->attributes[$uuid])->toString();
				}
			}
		}

		return $this;
	}

	/**
	 * Magic method to all protected/private class properties to be easily set,
	 * either through a direct access or a `setCamelCasedProperty()` method.
	 *
	 * Examples:
	 *
	 *      $this->my_property = $p;
	 *      $this->setMyProperty() = $p;
	 *
	 * @param string $key
	 * @param null   $value
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function __set(string $key, $value = null)
	{
		// Check if field is uuid field and in byte format
		if (! empty($this->uuids) && in_array($key, $this->uuids) && ! ctype_print($value))
		{
			$value = service('uuid')->fromBytes($value)->toString();
		}

		return parent::__set($key, $value);
	}

}

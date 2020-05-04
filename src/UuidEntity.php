<?php

namespace Michalsn\Uuid;

use CodeIgniter\Entity;

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
	 * Takes an array of key/value pairs and sets them as
	 * class properties, using any `setCamelCasedProperty()` methods
	 * that may or may not exist.
	 *
	 * @param array $data
	 *
	 * @return \CodeIgniter\Entity
	 */
	public function fill(array $data = null)
	{
		if (! is_array($data))
		{
			return $this;
		}

		// Load Uuid service
		$uuidObj = service('uuid');

		foreach ($data as $key => $value)
		{
			if (! empty($this->uuids) && in_array($key, $this->uuids) && ! ctype_print($value))
			{
				$value = $uuidObj->fromBytes($value)->toString();
			}

			$key = $this->mapProperty($key);

			$method = 'set' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $key)));

			if (method_exists($this, $method))
			{
				$this->$method($value);
			}
			else
			{
				$this->attributes[$key] = $value;
			}
		}

		return $this;
	}

}

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

		$key = $this->mapProperty($key);

		// Check if the field should be mutated into a date
		if (in_array($key, $this->dates))
		{
			$value = $this->mutateDate($value);
		}

		$isNullable = false;
		$castTo     = false;

		if (array_key_exists($key, $this->casts))
		{
			$isNullable = strpos($this->casts[$key], '?') === 0;
			$castTo     = $isNullable ? substr($this->casts[$key], 1) : $this->casts[$key];
		}

		if (! $isNullable || ! is_null($value))
		{
			// Array casting requires that we serialize the value
			// when setting it so that it can easily be stored
			// back to the database.
			if ($castTo === 'array')
			{
				$value = serialize($value);
			}

			// JSON casting requires that we JSONize the value
			// when setting it so that it can easily be stored
			// back to the database.
			if (($castTo === 'json' || $castTo === 'json-array') && function_exists('json_encode'))
			{
				$value = json_encode($value);

				if (json_last_error() !== JSON_ERROR_NONE)
				{
					throw CastException::forInvalidJsonFormatException(json_last_error());
				}
			}
		}

		// if a set* method exists for this key,
		// use that method to insert this value.
		// *) should be outside $isNullable check - SO maybe wants to do sth with null value automatically
		$method = 'set' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $key)));
		if (method_exists($this, $method))
		{
			$this->$method($value);

			return $this;
		}

		// Otherwise, just the value.
		// This allows for creation of new class
		// properties that are undefined, though
		// they cannot be saved. Useful for
		// grabbing values through joins,
		// assigning relationships, etc.
		$this->attributes[$key] = $value;

		return $this;
	}

}

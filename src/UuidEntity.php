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

		if (! empty($this->uuids))
		{
			// Load Uuid service
			$uuidObj = service('uuid');

			// Loop through the UUID array fields
			foreach ($this->uuids as $uuid)
			{
				// Check if field is in byte format
				if (mb_strlen($this->attributes[$uuid], 'UTF-8') < strlen($this->attributes[$uuid]))
				{
					$this->original[$uuid] = ($uuidObj->fromBytes($this->attributes[$uuid]))->toString();
				}
			}
		}

		return $this;
	}
}

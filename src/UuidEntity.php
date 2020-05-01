<?php

namespace Michalsn\UuidModel;

use CodeIgniter\Entity;
use Michalsn\UuidModel\Uuid;

/**
 * Entity encapsulation, for use with Michalsn\UuidModel\UuidModel
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
			$uuid = service('Uuid');

			// Loop through the UUID array fields
			foreach ($this->uuids as $uuid)
			{
				// Check if field is in byte format
				if (mb_strlen($this->attributes[$uuid], 'UTF-8') < strlen($this->attributes[$uuid]))
				{
					$this->original[$uuid] = ($uuid->fromBytes($this->attributes[$uuid]))->toString();
				}
			}
		}

		return $this;
	}
}

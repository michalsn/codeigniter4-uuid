<?php

namespace Tests\Support;

use Michalsn\Uuid\UuidEntity;

class Project2Entity extends UuidEntity
{
	protected $uuids = ['category_id'];

	protected $attributes = [
		'id' => null,
		'category_id' => null,
		'name' => null,
		'description' => null,
	];
}

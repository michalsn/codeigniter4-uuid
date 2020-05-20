<?php

namespace Tests\Support;

use Michalsn\Uuid\UuidEntity;

class Project1Entity extends UuidEntity
{
	protected $attributes = [
		'id' => null,
		'name' => null,
		'description' => null,
	];
}

<?php namespace Michalsn\UuidModel\Config;

use CodeIgniter\Config\BaseConfig;

class Uuid extends BaseConfig
{
	//--------------------------------------------------------------------
	// UUID version 1 optional config
	//--------------------------------------------------------------------
	// See more: https://uuid.ramsey.dev/en/latest/rfc4122/version1.html

	public $uuid1 = [
		'nodeProvider' => null,
		'clockSequence' => null
	];	

	//--------------------------------------------------------------------
	// UUID version 2 optional config
	//--------------------------------------------------------------------
	// See more: https://uuid.ramsey.dev/en/latest/rfc4122/version2.html

	public $uuid2 = [
		'localDomain' => null,
		'localIdentifier' => null,
		'nodeProvider' => null,
		'clockSequence' => null
	];

	//--------------------------------------------------------------------
	// UUID version 3 optional config
	//--------------------------------------------------------------------
	// See more: https://uuid.ramsey.dev/en/latest/rfc4122/version3.html

	public $uuid3 = [
		'ns' => null,
		'name' => null
	];

	//--------------------------------------------------------------------
	// UUID version 5 optional config
	//--------------------------------------------------------------------
	// See more: https://uuid.ramsey.dev/en/latest/rfc4122/version5.html

	public $uuid5 = [
		'ns' => null,
		'name' => null
	];

	//--------------------------------------------------------------------
	// UUID version 6 optional config
	//--------------------------------------------------------------------
	// See more: https://uuid.ramsey.dev/en/latest/nonstandard/version6.html

	public $uuid6 = [
		'nodeProvider' => null,
		'clockSequence' => null
	];
}
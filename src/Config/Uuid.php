<?php namespace Michalsn\Uuid\Config;

use CodeIgniter\Config\BaseConfig;

class Uuid extends BaseConfig
{
	//--------------------------------------------------------------------
	// Supported UUID versions
	//--------------------------------------------------------------------

	public $supportedVersions = ['uuid1', 'uuid2', 'uuid3', 'uuid4', 'uuid5', 'uuid6'];

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
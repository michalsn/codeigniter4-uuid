<?php

namespace Michalsn\UuidModel;

use Ramsey\Uuid\Uuid as RamseyUuid;

/**
 * Simple wrapper around Ramsey\Uuid class.
 */
class Uuid
{
	/**
	 * Config object.
	 *
	 * @var \Config\Uuid
	 */
	protected $config;

	//--------------------------------------------------------------------

	/**
	 * Prepare config to use
	 *
	 * @param \Config\Uuid $config
	 */
	public function __construct($config)
	{
		$this->config = $config;
	}

	/**
	 * UUID Version 1
	 *
	 * @param Hexadecimal|int|string|null $nodeProvider A 48-bit number representing the
     *     hardware address; this number may be represented as an integer or a
     *     hexadecimal string
     * @param int $clockSequence A 14-bit number used to help avoid duplicates that
     *     could arise when the clock is set backwards in time or if the node ID
     *     changes
	 *
	 * @return Ramsey\Uuid\UuidInterface
	 */
	public function uuid1($nodeProvider = null, ?int $clockSequence = null)
	{
		return RamseyUuid::uuid1(
			$nodeProvider ?? $this->config->uuid1['nodeProvider'], 
			$clockSequence ?? $this->config->uuid1['clockSequence']
		);
	}

	/**
	 * UUID Version 2
	 *
	 * @param int $localDomain The local domain to use when generating bytes,
     *     according to DCE Security
     * @param IntegerObject|null $localIdentifier The local identifier for the
     *     given domain; this may be a UID or GID on POSIX systems, if the local
     *     domain is person or group, or it may be a site-defined identifier
     *     if the local domain is org
     * @param Hexadecimal|null $node A 48-bit number representing the hardware
     *     address
     * @param int|null $clockSeq A 14-bit number used to help avoid duplicates
     *     that could arise when the clock is set backwards in time or if the
     *     node ID changes (in a version 2 UUID, the lower 8 bits of this number
     *     are replaced with the domain).
	 *
	 * @return Ramsey\Uuid\UuidInterface
	 */
	public function uuid2(
		int $localDomain, 
		?IntegerObject $localIdentifier = null, 
		?Hexadecimal $nodeProvider = null, 
		?int $clockSequence = null)
	{
		return RamseyUuid::uuid2(
			$localDomain ?? $this->config->uuid2['localDomain'],
			$localIdentifier ?? $this->config->uuid2['localIdentifier'],
			$nodeProvider ?? $this->config->uuid2['nodeProvider'],
			$clockSequence ?? $this->config->uuid2['clockSequence']
		);
	}

	/**
	 * UUID Version 3
	 *
	 * @param string|UuidInterface $ns The namespace (must be a valid UUID)
     * @param string $name The name to use for creating a UUID
	 *
	 * @return Ramsey\Uuid\UuidInterface
	 */
	public function uuid3($ns, string $name)
	{
		return RamseyUuid::uuid3(
			$ns ??$this->config->uuid3['ns'],
			$name ?? $this->config->uuid3['name']
		);
	}

	/**
	 * UUID Version 4
	 *
	 * @return Ramsey\Uuid\UuidInterface
	 */
	public function uuid4()
	{
		return RamseyUuid::uuid4();
	}

	/**
	 * UUID Version 5
	 *
	 * @param string|UuidInterface $ns The namespace (must be a valid UUID)
     * @param string $name The name to use for creating a UUID
	 *
	 * @return Ramsey\Uuid\UuidInterface
	 */
	public function uuid5($ns, string $name)
	{
		return RamseyUuid::uuid5(
			$ns ?? $this->config->uuid5['ns'],
			$name ?? $this->config->uuid5['name']
		);
	}

	/**
	 * UUID Version 6
	 *
	 * @param Hexadecimal|null $node A 48-bit number representing the hardware
     *     address
     * @param int $clockSeq A 14-bit number used to help avoid duplicates that
     *     could arise when the clock is set backwards in time or if the node ID
     *     changes
	 *
	 * @return Ramsey\Uuid\UuidInterface
	 */
	public function uuid6(
		?Hexadecimal $nodeProvider = null,
        ?int $clockSequence = null
    )
	{
		return RamseyUuid::uuid6(
			$nodeProvider ?? $this->config->uuid6['nodeProvider'], 
			$clockSequence ?? $this->config->uuid6['clockSequence']
		);
	}

	/**
	 * From string to UUID object
	 *
	 * @param string $uuid
	 *
	 * @return Ramsey\Uuid\UuidInterface
	 */
	public function fromString(string $uuid)
	{
		return RamseyUuid::fromString($uuid);
	}

	/**
	 * From byte string to UUID object
	 *
	 * @param string $bytes
	 *
	 * @return Ramsey\Uuid\UuidInterface
	 */
	public function fromBytes(string $bytes)
	{
		return RamseyUuid::fromBytes($bytes);
	}

	/**
	 * From 128-bit integer string to UUID object
	 *
	 * @param string $bytes
	 *
	 * @return Ramsey\Uuid\UuidInterface
	 */
	public function fromInteger(string $integer)
	{
		return RamseyUuid::fromInteger($integer);
	}

	/**
	 * Creates a UUID from a DateTimeInterface instance
	 *
	 * @return Ramsey\Uuid\UuidInterface
	 */
	public function fromDateTime(
        DateTimeInterface $dateTime,
        ?Hexadecimal $nodeProvider = null,
        ?int $clockSequence = null
    )
    {
    	return RamseyUuid::fromDateTime($dateTime, $nodeProvider, $clockSequence);
    }

    /**
     * Is valid UUID
     *
     * @param string $uuid
     *
     * @return bool
     */
    public function isValid(string $uuid)
    {
    	return RamseyUuid::isValid($uuid);
    }
}
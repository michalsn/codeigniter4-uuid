<?php

use Michalsn\Uuid\Uuid;

class UuidTest extends \CodeIgniter\Test\CIUnitTestCase
{
	protected $config;

	protected $uuid;

	public function setUp(): void
	{
		parent::setUp();

		$this->config = new \Michalsn\Uuid\Config\Uuid();
	}

	public function testUuid1()
	{
		$this->uuid = new Uuid($this->config);

        $uuid = $this->uuid->uuid1('0800200c9a66', 0x1669);
        $this->assertInstanceOf(\Ramsey\Uuid\Rfc4122\UuidV1::class, $uuid);
        $this->assertInstanceOf(DateTimeInterface::class, $uuid->getDateTime());
        $this->assertEquals(2, $uuid->getVariant());
        $this->assertEquals(1, $uuid->getVersion());
        $this->assertEquals(5737, $uuid->getClockSequence());
        $this->assertSame('8796630719078', $uuid->getNode());
        $this->assertEquals('9669-0800200c9a66', substr($uuid->toString(), 19));
	}

	public function testUuid3()
	{
		$this->uuid = new Uuid($this->config);

		$uuid = $this->uuid->uuid3(\Ramsey\Uuid\Uuid::NIL, '0');
		$this->assertInstanceOf(\Ramsey\Uuid\Rfc4122\UuidV3::class, $uuid);

        $this->assertEquals('19826852-5007-3022-a72a-212f66e9fac3', $uuid->toString());
	}

	public function testUuid4()
	{
		$this->uuid = new Uuid($this->config);

		$uuid = $this->uuid->uuid4();
        $this->assertInstanceOf(\Ramsey\Uuid\Rfc4122\UuidV4::class, $uuid);
        $this->assertEquals(2, $uuid->getVariant());
        $this->assertEquals(4, $uuid->getVersion());
    }

    public function testUuid5()
	{
		$uuid = '5f890891-ea7c-5669-ac4e-65767fe71a93';
        $ns = \Ramsey\Uuid\Uuid::NAMESPACE_DNS;
        $name = 'codeigniter.com';

		$this->uuid = new Uuid($this->config);

		$uobj1 = $this->uuid->uuid5($ns, $name);
        $uobj2 = $this->uuid->uuid5($this->uuid->fromString($ns), $name);

        $this->assertEquals(2, $uobj1->getVariant());
        $this->assertEquals(5, $uobj1->getVersion());
        $this->assertEquals($this->uuid->fromString($uuid), $uobj1);
        $this->assertEquals((string) $uobj1, $uuid);
        $this->assertTrue($uobj1->equals($uobj2));
	}

	public function testUuid6()
	{
		$this->uuid = new Uuid($this->config);

		$uuid = $this->uuid->uuid6(new \Ramsey\Uuid\Type\Hexadecimal('0800200c9a66'), 0x1669);
        $this->assertInstanceOf(\Ramsey\Uuid\Nonstandard\UuidV6::class, $uuid);
        $this->assertInstanceOf(DateTimeInterface::class, $uuid->getDateTime());
        $this->assertSame(2, $uuid->getVariant());
        $this->assertSame(6, $uuid->getVersion());
        $this->assertSame('1669', $uuid->getClockSequenceHex());
        $this->assertSame('0800200c9a66', $uuid->getNodeHex());
        $this->assertSame('9669-0800200c9a66', substr($uuid->toString(), 19));
	}

	public function testFromString()
    {
    	$this->uuid = new Uuid($this->config);

        $uuid = $this->uuid->fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $this->assertInstanceOf(\Ramsey\Uuid\Rfc4122\UuidV1::class, $uuid);
        $this->assertEquals('ff6f8cb0-c57d-11e1-9b21-0800200c9a66', $uuid->toString());
    }

	public function testFromBytes()
    {
    	$this->uuid = new Uuid($this->config);

        $uuid =  $this->uuid->fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $bytes = $uuid->getBytes();

        $fromBytesUuid = $this->uuid->fromBytes($bytes);

        $this->assertTrue($uuid->equals($fromBytesUuid));
    }

    public function testFromInteger()
    {
    	$this->uuid = new Uuid($this->config);

        $uuid = $this->uuid->fromString('ff6f8cb0-c57d-11e1-9b21-0800200c9a66');
        $integer = $uuid->getInteger()->toString();

        $fromIntegerUuid = $this->uuid->fromInteger($integer);

        $this->assertTrue($uuid->equals($fromIntegerUuid));
    }

    public function testFromDateTime()
    {
    	$this->uuid = new Uuid($this->config);

        $uuid = $this->uuid->fromString('ff6f8cb0-c57d-11e1-8b21-0800200c9a66');
        $dateTime = $uuid->getDateTime();

        $fromDateTimeUuid = $this->uuid->fromDateTime($dateTime, new \Ramsey\Uuid\Type\Hexadecimal('0800200c9a66'), 2849);

        $this->assertTrue($uuid->equals($fromDateTimeUuid));
    }

    public function testIsValid()
    {
        $this->uuid = new Uuid($this->config);

        $result = $this->uuid->isValid('ff6f8cb0-c57d-11e1-8b21-0800200c9a66');
        $this->assertTrue($result);
        
        $result = $this->uuid->isValid('ff6f8cb0-0800200c9a66');
        $this->assertFalse($result);
    }
}
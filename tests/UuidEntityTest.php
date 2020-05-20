<?php

use Michalsn\Uuid\Uuid;
use Tests\Support\Project1Entity;
use Tests\Support\Project2Entity;

class UuidEntityTest extends \CodeIgniter\Test\CIUnitTestCase
{
	protected $uuid;

	public function setUp(): void
	{
		parent::setUp();

		$config = new \Michalsn\Uuid\Config\Uuid();
		$this->uuid = new Uuid($config);
	}

	public function testFilledConstruction()
	{
		$data = [
			'id'  => $this->uuid->fromString('bb0ed7fc-888d-11ea-b597-0800273b4cc5')->getBytes(),
			'name' => 'Sample name',
			'description' => 'Sample description',
		];

		$p1 = new Project1Entity($data);
		$this->assertEquals('bb0ed7fc-888d-11ea-b597-0800273b4cc5', $p1->id);
		$this->assertEquals('Sample name', $p1->name);
		$this->assertEquals('Sample description', $p1->description);

		$this->assertTrue($p1->hasChanged('id'));
		$this->assertTrue($p1->hasChanged('name'));
		$this->assertTrue($p1->hasChanged('description'));
	}

	public function testFillWithSyncOriginal()
	{
		$p1 = new Project1Entity();

		$data = [
			'id'  => $this->uuid->fromString('bb0ed7fc-888d-11ea-b597-0800273b4cc5')->getBytes(),
			'name' => 'Sample name',
			'description' => 'Sample description',
		];

		$p1->fill($data)->syncOriginal();

		$this->assertEquals('bb0ed7fc-888d-11ea-b597-0800273b4cc5', $p1->id);
		$this->assertEquals('Sample name', $p1->name);
		$this->assertEquals('Sample description', $p1->description);

		$this->assertFalse($p1->hasChanged('id'));
		$this->assertFalse($p1->hasChanged('name'));
		$this->assertFalse($p1->hasChanged('description'));
	}

	public function testFilledConstructionWithReadyData()
	{
		$data = [
			'id'  => 'bb0ed7fc-888d-11ea-b597-0800273b4cc5',
			'name' => 'Sample name',
			'description' => 'Sample description',
		];

		$p1 = new Project1Entity($data);
		$this->assertEquals('bb0ed7fc-888d-11ea-b597-0800273b4cc5', $p1->id);
		$this->assertEquals('Sample name', $p1->name);
		$this->assertEquals('Sample description', $p1->description);
	}

	public function testNonDefaultUuidField()
	{
		$data = [
			'id' => 1,
			'category_id' => $this->uuid->fromString('bb0ed7fc-888d-11ea-b597-0800273b4cc5')->getBytes(),
			'name' => 'Sample name',
			'description' => 'Sample description',
		];

		$p2 = new Project2Entity($data);
		$this->assertEquals(1, $p2->id);
		$this->assertEquals('bb0ed7fc-888d-11ea-b597-0800273b4cc5', $p2->category_id);
		$this->assertEquals('Sample name', $p2->name);
		$this->assertEquals('Sample description', $p2->description);
	}
}
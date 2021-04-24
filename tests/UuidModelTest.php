<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Michalsn\Uuid\Uuid;
use Tests\Support\Project1Model;
use Tests\Support\Project2Model;

class UuidModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    
    protected $refresh   = true;
    protected $seed      = 'Tests\Support\Database\Seeds\UuidSeeder';
    protected $basePath  = SUPPORTPATH . 'Database/';
    protected $namespace = 'Tests\Support';

    // Projects1
    public function testInsertWithUuidPrimaryKey()
    {
        $projectModel = new Project1Model();
        
        $data = [
            'name' => 'Sample name',
            'description' => 'Sample description',
        ];

        $projectId = $projectModel->insert($data);

        $result = $projectModel->find($projectId);
        unset($result['created_at'], $result['updated_at'], $result['deleted_at']);

        $expected = [
            'id' => $projectId,
            'name' => $data['name'],
            'description' => $data['description'],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testUpdateWithUuidPrimaryKey()
    {
        $projectModel = new Project1Model();
        $config = new \Michalsn\Uuid\Config\Uuid();
        $uuid = new Uuid($config);

        $row = $projectModel->update('c2b8c2a8-2fc3-a2c3-bf22-4d2ec2b6c394', ['name' => 'updated']);

        $expected = [
            'id'           => $uuid->fromString('c2b8c2a8-2fc3-a2c3-bf22-4d2ec2b6c394')->getBytes(),
            'name'         => 'updated',
            'description'  => 'Description 1',
        ];

        $this->seeInDatabase('projects1', $expected);
    }

    public function testDeleteWithUuidPrimaryKey()
    {
        $projectModel = new Project1Model();
        $config = new \Michalsn\Uuid\Config\Uuid();
        $uuid = new Uuid($config);

        $row = $projectModel->delete('c2b8c2a8-2fc3-a2c3-bf22-4d2ec2b6c394');

        $expected1 = [
            'id' => $uuid->fromString('c2b8c2a8-2fc3-a2c3-bf22-4d2ec2b6c394')->getBytes(),
            'deleted_at' => null,
        ];

        $expected2 = [
            'id' => $uuid->fromString('c2b8c2a8-2fc3-a2c3-bf22-4d2ec2b6c394')->getBytes(),
        ];

        $this->dontSeeInDatabase('projects1', $expected1);
        $this->seeInDatabase('projects1', $expected2);
    }

    public function testDeleteWithoutSoftDeleteWithUuidPrimaryKey()
    {
        $projectModel = new Project1Model();
        $config = new \Michalsn\Uuid\Config\Uuid();
        $uuid = new Uuid($config);

        $this->setPrivateProperty($projectModel, 'useSoftDeletes', false);

        $row = $projectModel->delete('c2b8c2a8-2fc3-a2c3-bf22-4d2ec2b6c394');

        $expected = [
            'id' => $uuid->fromString('c2b8c2a8-2fc3-a2c3-bf22-4d2ec2b6c394')->getBytes(),
        ];

        $this->dontSeeInDatabase('projects1', $expected);
    }

    public function testFindWithUuidPrimaryKey()
    {
        $projectModel = new Project1Model();

        $row = $projectModel->find('c2b8c2a8-2fc3-a2c3-bf22-4d2ec2b6c394');

        $expected = [
            'id'           => 'c2b8c2a8-2fc3-a2c3-bf22-4d2ec2b6c394',
            'name'         => 'Name 1',
            'description'  => 'Description 1',
        ];

        unset($row['created_at'], $row['updated_at'], $row['deleted_at']);

        $this->assertEquals($expected, $row);
    }

    public function testFindAllWithUuidPrimaryKey()
    {
        $projectModel = new Project1Model();

        $results = $projectModel->orderBy('created_at')->findAll();

        $expected = [
            [
                'id'           => 'c2b8c2a8-2fc3-a2c3-bf22-4d2ec2b6c394',
                'name'         => 'Name 1',
                'description'  => 'Description 1',
            ],
            [
                'id'           => 'c2912425-c3aa-7774-4dc2-aac28654c3a1',
                'name'         => 'Name 2',
                'description'  => 'Description 2',
            ],
            [
                'id'           => 'c38ac295-c2b9-c384-c398-c28a4416c2b5',
                'name'         => 'Name 3',
                'description'  => 'Description 3',
            ],
        ];

        foreach ($results as &$row)
        {
            unset($row['created_at'], $row['updated_at'], $row['deleted_at']);
        }

        $this->assertEquals($expected, $results);
    }

    public function testFirstWithUuidPrimaryKey()
    {
        $projectModel = new Project1Model();

        $row = $projectModel->first();

        $expected = [
            'id'           => 'c2b8c2a8-2fc3-a2c3-bf22-4d2ec2b6c394',
            'name'         => 'Name 1',
            'description'  => 'Description 1',
        ];

        unset($row['created_at'], $row['updated_at'], $row['deleted_at']);

        $this->assertEquals($expected, $row);
    }

    public function testInsertBatchWithUuidPrimaryKey()
    {
        $projectModel = new Project1Model();

        $inserts = [
            [
                'name'         => 'Name 4',
                'description'  => 'Description 4',
            ],
            [
                'name'         => 'Name 5',
                'description'  => 'Description 5',
            ],
            [
                'name'         => 'Name 6',
                'description'  => 'Description 6',
            ],
        ];

        $results = $projectModel->insertBatch($inserts);

        $this->assertEquals(3, $results);

        $this->seeNumRecords(6, 'projects1', ['deleted_at' => null]);
    }

    public function testUpdateBatchWithUuidPrimaryKey()
    {
        $projectModel = new Project1Model();

        $updates = [
            [
                'id'           => 'c2b8c2a8-2fc3-a2c3-bf22-4d2ec2b6c394',
                'name'         => 'Name 1 updated',
                'description'  => 'Description 1',
            ],
            [
                'id'           => 'c2912425-c3aa-7774-4dc2-aac28654c3a1',
                'name'         => 'Name 2 updated',
                'description'  => 'Description 2',
            ],
            [
                'id'           => 'c38ac295-c2b9-c384-c398-c28a4416c2b5',
                'name'         => 'Name 3 updated',
                'description'  => 'Description 3',
            ],
        ];

        $results = $projectModel->updateBatch($updates, 'id');

        $this->assertEquals(3, $results);

        $this->seeInDatabase('projects1', ['name' => 'Name 1 updated']);
        $this->seeInDatabase('projects1', ['name' => 'Name 2 updated']);
        $this->seeInDatabase('projects1', ['name' => 'Name 3 updated']);
    }

    // Projects2
    public function testInsertWithoutUuidPrimaryKey()
    {
        $projectModel = new Project2Model();
        
        $data = [
            'category_id' => 'c2b8c2a8-2fc3-a2c3-bf22-4d2ec2b6c394',
            'name' => 'Sample name',
            'description' => 'Sample description',
        ];

        $projectId = $projectModel->insert($data);

        $result = $projectModel->find($projectId);
        unset($result['created_at'], $result['updated_at'], $result['deleted_at']);

        $expected = [
            'id' => (string) $projectId,
            'category_id' => 'c2b8c2a8-2fc3-a2c3-bf22-4d2ec2b6c394',
            'name' => $data['name'],
            'description' => $data['description'],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testUpdateWithoutUuidPrimaryKey()
    {
        $projectModel = new Project2Model();
        $config = new \Michalsn\Uuid\Config\Uuid();
        $uuid = new Uuid($config);

        $row = $projectModel->update(1, [
            'name' => 'updated', 'category_id' => 'c2912425-c3aa-7774-4dc2-aac28654c3a1'
        ]);

        $expected = [
            'id'           => '1',
            'category_id'  => $uuid->fromString('c2912425-c3aa-7774-4dc2-aac28654c3a1')->getBytes(),
            'name'         => 'updated',
            'description'  => 'Description 1',
        ];

        $this->seeInDatabase('projects2', $expected);
    }

    public function testDeleteWithoutUuidPrimaryKey()
    {
        $projectModel = new Project2Model();
        $config = new \Michalsn\Uuid\Config\Uuid();
        $uuid = new Uuid($config);

        $row = $projectModel->delete(1);

        $expected1 = [
            'id'          => '1',
            'category_id' => $uuid->fromString('c2b8c2a8-2fc3-a2c3-bf22-4d2ec2b6c394')->getBytes(),
            'deleted_at'  => null,
        ];

        $expected2 = [
            'category_id' => $uuid->fromString('c2b8c2a8-2fc3-a2c3-bf22-4d2ec2b6c394')->getBytes(),
        ];

        $this->dontSeeInDatabase('projects2', $expected1);
        $this->seeInDatabase('projects2', $expected2);
    }

    public function testDeleteWithoutSoftDeleteWithoutUuidPrimaryKey()
    {
        $projectModel = new Project2Model();

        $this->setPrivateProperty($projectModel, 'useSoftDeletes', false);

        $row = $projectModel->delete(1);

        $expected = [
            'id' => 1,
        ];

        $this->dontSeeInDatabase('projects2', $expected);
    }

    public function testFindWithoutUuidPrimaryKey()
    {
        $projectModel = new Project2Model();

        $row = $projectModel->find(1);

        $expected = [
            'id'           => '1',
            'category_id'  => 'c2b8c2a8-2fc3-a2c3-bf22-4d2ec2b6c394',
            'name'         => 'Name 1',
            'description'  => 'Description 1',
        ];

        unset($row['created_at'], $row['updated_at'], $row['deleted_at']);

        $this->assertEquals($expected, $row);
    }

    public function testFindAllWithoutUuidPrimaryKey()
    {
        $projectModel = new Project2Model();

        $results = $projectModel->orderBy('created_at')->findAll();

        $expected = [
            [
                'id'           => '1',
                'category_id'  => 'c2b8c2a8-2fc3-a2c3-bf22-4d2ec2b6c394',
                'name'         => 'Name 1',
                'description'  => 'Description 1',
            ],
            [
                'id'           => '2',
                'category_id'  => 'c2912425-c3aa-7774-4dc2-aac28654c3a1',
                'name'         => 'Name 2',
                'description'  => 'Description 2',
            ],
            [
                'id'           => '3',
                'category_id'  => 'c38ac295-c2b9-c384-c398-c28a4416c2b5',
                'name'         => 'Name 3',
                'description'  => 'Description 3',
            ],
        ];

        foreach ($results as &$row)
        {
            unset($row['created_at'], $row['updated_at'], $row['deleted_at']);
        }

        $this->assertEquals($expected, $results);
    }

    public function testFirstWithoutUuidPrimaryKey()
    {
        $projectModel = new Project2Model();

        $row = $projectModel->first();

        $expected = [
            'id'           => '1',
            'category_id'  => 'c2b8c2a8-2fc3-a2c3-bf22-4d2ec2b6c394',
            'name'         => 'Name 1',
            'description'  => 'Description 1',
        ];

        unset($row['created_at'], $row['updated_at'], $row['deleted_at']);

        $this->assertEquals($expected, $row);
    }

    public function testInsertBatchWithoutUuidPrimaryKey()
    {
        $projectModel = new Project2Model();

        $inserts = [
            [
                'category_id'  => 'c2b8c2a8-2fc3-a2c3-bf22-4d2ec2b6c394',
                'name'         => 'Name 4',
                'description'  => 'Description 4',
            ],
            [
                'category_id'  => 'c2912425-c3aa-7774-4dc2-aac28654c3a1',
                'name'         => 'Name 5',
                'description'  => 'Description 5',
            ],
            [
                'category_id'  => 'c38ac295-c2b9-c384-c398-c28a4416c2b5',
                'name'         => 'Name 6',
                'description'  => 'Description 6',
            ],
        ];

        $results = $projectModel->insertBatch($inserts);

        $this->assertEquals(3, $results);

        $this->seeNumRecords(6, 'projects2', ['deleted_at' => null]);
    }

    public function testUpdateBatchWithoutUuidPrimaryKey()
    {
        $projectModel = new Project2Model();
        $config = new \Michalsn\Uuid\Config\Uuid();
        $uuid = new Uuid($config);

        $updates = [
            [
                'id'           => '1',
                'category_id'  => 'c2b8c2a8-2fc3-a2c3-bf22-4d2ec2b6c394',
                'name'         => 'Name 1 updated',
                'description'  => 'Description 1',
            ],
            [
                'id'           => '2',
                'category_id'  => 'c2912425-c3aa-7774-4dc2-aac28654c3a1',
                'name'         => 'Name 2 updated',
                'description'  => 'Description 2',
            ],
            [
                'id'           => '3',
                'category_id'  => 'c38ac295-c2b9-c384-c398-c28a4416c2b5',
                'name'         => 'Name 3 updated',
                'description'  => 'Description 3',
            ],
        ];

        $results = $projectModel->updateBatch($updates, 'id');

        $this->assertEquals(3, $results);

        $this->seeInDatabase('projects2', [
            'name' => 'Name 1 updated', 
            'category_id' => $uuid->fromString('c2b8c2a8-2fc3-a2c3-bf22-4d2ec2b6c394')->getBytes(),
        ]);
        $this->seeInDatabase('projects2', [
            'name' => 'Name 2 updated', 
            'category_id' => $uuid->fromString('c2912425-c3aa-7774-4dc2-aac28654c3a1')->getBytes(),
        ]);
        $this->seeInDatabase('projects2', [
            'name' => 'Name 3 updated', 
            'category_id' => $uuid->fromString('c38ac295-c2b9-c384-c398-c28a4416c2b5')->getBytes(),
        ]);
    }

}
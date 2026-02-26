<?php

declare(strict_types=1);

namespace Tests;

use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Models\Project1Model;
use Tests\Support\Models\Project2Model;
use Tests\Support\Models\Project3Model;
use Tests\Support\Models\Project4Model;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class UuidModelTest extends TestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $namespace;

    public function testInsertWithUuidPrimaryKeyString()
    {
        $model = model(Project1Model::class);

        $data = [
            'name'        => 'Test Project',
            'description' => 'Test Description',
        ];

        $id = $model->insert($data);

        $this->assertIsString($id);
        $this->assertSame(36, strlen($id));

        $this->seeInDatabase('projects1', [
            'id'          => $id,
            'name'        => 'Test Project',
            'description' => 'Test Description',
        ]);
    }

    public function testInsertWithUuidPrimaryKeyBytes()
    {
        $model = model(Project3Model::class);

        $data = [
            'name'        => 'Test Project Bytes',
            'description' => 'Test Description Bytes',
        ];

        $id = $model->insert($data);

        $this->assertIsString($id);
        $this->assertSame(36, strlen($id));

        // The ID should be converted back to string for return
        $project = $model->find($id);
        $this->assertIsArray($project);
        $this->assertSame('Test Project Bytes', $project['name']);
    }

    public function testFindWithUuidPrimaryKeyString()
    {
        $model = model(Project1Model::class);

        $id = $model->insert([
            'name'        => 'Find Test',
            'description' => 'Find Description',
        ]);

        $project = $model->find($id);

        $this->assertIsArray($project);
        $this->assertSame($id, $project['id']);
        $this->assertSame('Find Test', $project['name']);
    }

    public function testFindWithUuidPrimaryKeyBytes()
    {
        $model = model(Project3Model::class);

        $id = $model->insert([
            'name'        => 'Find Test Bytes',
            'description' => 'Find Description Bytes',
        ]);

        $project = $model->find($id);

        $this->assertIsArray($project);
        $this->assertSame($id, $project['id']);
        $this->assertSame('Find Test Bytes', $project['name']);
    }

    public function testFindAllWithUuidPrimaryKey()
    {
        $model = model(Project1Model::class);

        $model->insert([
            'name'        => 'Project 1',
            'description' => 'Description 1',
        ]);

        $model->insert([
            'name'        => 'Project 2',
            'description' => 'Description 2',
        ]);

        $projects = $model->findAll();

        $this->assertCount(2, $projects);
        $this->assertIsArray($projects[0]);
        $this->assertArrayHasKey('id', $projects[0]);
        $this->assertSame(36, strlen((string) $projects[0]['id']));
    }

    public function testFindMultipleWithUuidPrimaryKey()
    {
        $model = model(Project1Model::class);

        $id1 = $model->insert([
            'name'        => 'Project 1',
            'description' => 'Description 1',
        ]);

        $id2 = $model->insert([
            'name'        => 'Project 2',
            'description' => 'Description 2',
        ]);

        $projects = $model->find([$id1, $id2]);

        $this->assertCount(2, $projects);
        $this->assertSame($id1, $projects[0]['id']);
        $this->assertSame($id2, $projects[1]['id']);
    }

    public function testUpdateWithUuidPrimaryKeyString()
    {
        $model = model(Project1Model::class);

        $id = $model->insert([
            'name'        => 'Original Name',
            'description' => 'Original Description',
        ]);

        $result = $model->update($id, [
            'name' => 'Updated Name',
        ]);

        $this->assertTrue($result);

        $this->seeInDatabase('projects1', [
            'id'   => $id,
            'name' => 'Updated Name',
        ]);
    }

    public function testUpdateWithUuidPrimaryKeyBytes()
    {
        $model = model(Project3Model::class);

        $id = $model->insert([
            'name'        => 'Original Name Bytes',
            'description' => 'Original Description Bytes',
        ]);

        $result = $model->update($id, [
            'name' => 'Updated Name Bytes',
        ]);

        $this->assertTrue($result);

        $project = $model->find($id);

        $this->assertSame('Updated Name Bytes', $project['name']);
    }

    public function testSoftDeleteWithUuidPrimaryKey()
    {
        $model = model(Project1Model::class);

        $id = $model->insert([
            'name'        => 'To Delete',
            'description' => 'To Delete Description',
        ]);

        $result = $model->delete($id);

        $this->assertTrue($result);

        // Should not find it by default
        $project = $model->find($id);
        $this->assertNull($project);

        // Should find it with withDeleted()
        $project = $model->withDeleted()->find($id);
        $this->assertIsArray($project);
        $this->assertNotNull($project['deleted_at']);
    }

    public function testHardDeleteWithUuidPrimaryKey()
    {
        $model = model(Project1Model::class);

        $id = $model->insert([
            'name'        => 'To Hard Delete',
            'description' => 'To Hard Delete Description',
        ]);

        $result = $model->delete($id, true);

        $this->assertTrue($result);

        // Should not find it even with withDeleted()
        $project = $model->withDeleted()->find($id);
        $this->assertNull($project);
    }

    public function testInsertBatchWithUuidPrimaryKey()
    {
        $model = model(Project1Model::class);

        $data = [
            [
                'name'        => 'Batch Project 1',
                'description' => 'Batch Description 1',
            ],
            [
                'name'        => 'Batch Project 2',
                'description' => 'Batch Description 2',
            ],
            [
                'name'        => 'Batch Project 3',
                'description' => 'Batch Description 3',
            ],
        ];

        $result = $model->insertBatch($data);

        $this->assertSame(3, $result);

        $projects = $model->findAll();
        $this->assertCount(3, $projects);

        // All should have UUIDs
        foreach ($projects as $project) {
            $this->assertSame(36, strlen((string) $project['id']));
        }
    }

    public function testInsertBatchWithUuidPrimaryKeyBytes()
    {
        $model = model(Project3Model::class);

        $data = [
            [
                'name'        => 'Batch Bytes 1',
                'description' => 'Batch Bytes Description 1',
            ],
            [
                'name'        => 'Batch Bytes 2',
                'description' => 'Batch Bytes Description 2',
            ],
        ];

        $result = $model->insertBatch($data);

        $this->assertSame(2, $result);

        $projects = $model->findAll();
        $this->assertCount(2, $projects);

        // All should have UUIDs converted to string format
        foreach ($projects as $project) {
            $this->assertSame(36, strlen((string) $project['id']));
        }
    }

    public function testUuidOnCustomFieldString()
    {
        $model = model(Project2Model::class);

        $data = [
            'category_id' => service('uuid')->generate('v4')->toString(),
            'name'        => 'Custom Field Test',
            'description' => 'Custom Field Description',
        ];

        $id = $model->insert($data);

        $this->assertIsInt($id);

        $project = $model->find($id);
        $this->assertSame($data['category_id'], $project['category_id']);
        $this->assertSame(36, strlen((string) $project['category_id']));
    }

    public function testUuidOnCustomFieldBytes()
    {
        $model = model(Project4Model::class);

        $categoryId = service('uuid')->generate('v7')->toString();
        $data       = [
            'category_id' => $categoryId,
            'name'        => 'Custom Field Bytes Test',
            'description' => 'Custom Field Bytes Description',
        ];

        $id = $model->insert($data);

        $this->assertIsInt($id);

        $project = $model->find($id);
        $this->assertSame($categoryId, $project['category_id']);
        $this->assertSame(36, strlen((string) $project['category_id']));
    }

    public function testValidationFailure()
    {
        $model = model(Project1Model::class);

        $data = [
            'name'        => 'AB', // Too short (min_length is 3)
            'description' => '',   // Required
        ];

        $result = $model->insert($data);

        $this->assertFalse($result);

        $errors = $model->errors();
        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('description', $errors);
    }

    public function testSaveMethodWithUuidPrimaryKey()
    {
        $model = model(Project1Model::class);

        // Insert via save
        $data = [
            'name'        => 'Save Test',
            'description' => 'Save Description',
        ];

        $result = $model->save($data);
        $this->assertTrue($result);

        // Update via save
        $projects = $model->findAll();
        $project  = $projects[0];
        $id       = $project['id'];

        $project['name'] = 'Updated via Save';
        $result          = $model->save($project);

        $this->assertTrue($result);

        $this->seeInDatabase('projects1', [
            'id'   => $id,
            'name' => 'Updated via Save',
        ]);
    }

    public function testSaveMethodWithUuidPrimaryKeyBytes()
    {
        $model = model(Project3Model::class);

        // Insert via save
        $data = [
            'name'        => 'Save Test Bytes',
            'description' => 'Save Description Bytes',
        ];

        $result = $model->save($data);
        $this->assertTrue($result);

        // Update via save — this triggers shouldUpdate() with binary PK
        $projects = $model->findAll();
        $project  = $projects[0];
        $id       = $project['id'];

        $project['name'] = 'Updated via Save Bytes';
        $result          = $model->save($project);

        $this->assertTrue($result);

        $updated = $model->find($id);
        $this->assertSame('Updated via Save Bytes', $updated['name']);
    }

    public function testFirstMethodReturnsOldestRecord()
    {
        $model = model(Project1Model::class);

        $model->insert([
            'name'        => 'First Project',
            'description' => 'First Description',
        ]);

        sleep(1); // Ensure different timestamps

        $model->insert([
            'name'        => 'Second Project',
            'description' => 'Second Description',
        ]);

        $project = $model->first();

        $this->assertIsArray($project);
        // For UUID v7 or with timestamps, first() should return the oldest
        $this->assertSame('First Project', $project['name']);
    }

    public function testWhereWithUuidPrimaryKey()
    {
        $model = model(Project1Model::class);

        $id = $model->insert([
            'name'        => 'Where Test',
            'description' => 'Where Description',
        ]);

        $project = $model->where('id', $id)->first();

        $this->assertIsArray($project);
        $this->assertSame($id, $project['id']);
        $this->assertSame('Where Test', $project['name']);
    }

    public function testCountAllWithUuidPrimaryKey()
    {
        $model = model(Project1Model::class);

        $model->insert([
            'name'        => 'Count Test 1',
            'description' => 'Count Description 1',
        ]);

        $model->insert([
            'name'        => 'Count Test 2',
            'description' => 'Count Description 2',
        ]);

        $count = $model->builder()->countAll();

        $this->assertSame(2, $count);
    }
}

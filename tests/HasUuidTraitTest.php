<?php

declare(strict_types=1);

namespace Tests;

use CodeIgniter\Model;
use CodeIgniter\Test\DatabaseTestTrait;
use Michalsn\CodeIgniterUuid\Enums\UuidType;
use Michalsn\CodeIgniterUuid\Enums\UuidVersion;
use Michalsn\CodeIgniterUuid\Exceptions\CodeIgniterUuidException;
use Michalsn\CodeIgniterUuid\Models\Cast\UuidCast;
use Michalsn\CodeIgniterUuid\Traits\HasUuid;
use ReflectionClass;
use Tests\Support\Models\Project1Model;
use Tests\Support\Models\Project2Model;
use Tests\Support\Models\Project3Model;
use Tests\Support\TestCase;

/**
 * @internal
 */
final class HasUuidTraitTest extends TestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $namespace;

    public function testModelInitializesUuidHandlers()
    {
        $model = model(Project1Model::class);

        $reflection   = new ReflectionClass($model);
        $property     = $reflection->getProperty('castHandlers');
        $castHandlers = $property->getValue($model);

        $this->assertArrayHasKey('uuid', $castHandlers);
        $this->assertSame(UuidCast::class, $castHandlers['uuid']);
    }

    public function testUuidFieldsAreParsedCorrectly()
    {
        $model = model(Project1Model::class);

        $reflection = new ReflectionClass($model);
        $property   = $reflection->getProperty('uuidFields');
        $uuidFields = $property->getValue($model);

        $this->assertArrayHasKey('id', $uuidFields);
        $this->assertIsArray($uuidFields['id']);
        $this->assertArrayHasKey('version', $uuidFields['id']);
        $this->assertArrayHasKey('type', $uuidFields['id']);
    }

    public function testUuidFieldsWithCustomVersionAndType()
    {
        $model = model(Project3Model::class);

        $reflection = new ReflectionClass($model);
        $property   = $reflection->getProperty('uuidFields');
        $uuidFields = $property->getValue($model);

        $this->assertSame(UuidVersion::V7, $uuidFields['id']['version']);
        $this->assertSame(UuidType::BYTES, $uuidFields['id']['type']);
    }

    public function testUuidFieldOnNonPrimaryKey()
    {
        $model = model(Project2Model::class);

        $reflection = new ReflectionClass($model);
        $property   = $reflection->getProperty('uuidFields');
        $uuidFields = $property->getValue($model);

        $this->assertArrayHasKey('category_id', $uuidFields);
        $this->assertArrayNotHasKey('id', $uuidFields);
    }

    public function testUuidGenerationWithStringType()
    {
        $model = model(Project1Model::class);

        $data = [
            'name'        => 'UUID String Test',
            'description' => 'UUID String Description',
        ];

        $id = $model->insert($data);

        $this->assertIsString($id);
        $this->assertSame(36, strlen($id));
        // V7 UUIDs have version digit '7' at position 14 (0-indexed)
        $this->assertSame('7', $id[14]);
    }

    public function testUuidGenerationWithBytesType()
    {
        $model = model(Project3Model::class);

        $data = [
            'name'        => 'UUID Bytes Test',
            'description' => 'UUID Bytes Description',
        ];

        $id = $model->insert($data);

        // Even with bytes storage, the return should be string format
        $this->assertIsString($id);
        $this->assertSame(36, strlen($id));
    }

    public function testUuidConversionForBytesInFind()
    {
        $model = model(Project3Model::class);

        $id = $model->insert([
            'name'        => 'Conversion Test',
            'description' => 'Conversion Description',
        ]);

        // Find should accept string UUID and convert to bytes for query
        $project = $model->find($id);

        $this->assertIsArray($project);
        $this->assertSame($id, $project['id']);
    }

    public function testUuidConversionForBytesInUpdate()
    {
        $model = model(Project3Model::class);

        $id = $model->insert([
            'name'        => 'Update Conversion Test',
            'description' => 'Update Conversion Description',
        ]);

        // Update should accept string UUID
        $result = $model->update($id, [
            'name' => 'Updated Name',
        ]);

        $this->assertTrue($result);

        $project = $model->find($id);
        $this->assertSame('Updated Name', $project['name']);
    }

    public function testUuidConversionForBytesInDelete()
    {
        $model = model(Project3Model::class);

        $id = $model->insert([
            'name'        => 'Delete Conversion Test',
            'description' => 'Delete Conversion Description',
        ]);

        // Delete should accept string UUID
        $result = $model->delete($id);

        $this->assertTrue($result);

        $project = $model->find($id);
        $this->assertNull($project);
    }

    public function testBatchInsertGeneratesUniqueUuids()
    {
        $model = model(Project1Model::class);

        $data = [
            ['name' => 'Batch 1', 'description' => 'Description 1'],
            ['name' => 'Batch 2', 'description' => 'Description 2'],
            ['name' => 'Batch 3', 'description' => 'Description 3'],
            ['name' => 'Batch 4', 'description' => 'Description 4'],
            ['name' => 'Batch 5', 'description' => 'Description 5'],
        ];

        $result = $model->insertBatch($data);

        $this->assertSame(5, $result);

        $projects = $model->findAll();
        $ids      = array_column($projects, 'id');

        // All IDs should be unique
        $this->assertCount(5, $ids);
        $this->assertCount(5, array_unique($ids));

        // All IDs should be valid UUIDs
        foreach ($ids as $id) {
            $this->assertSame(36, strlen((string) $id));
        }
    }

    public function testMultipleUuidFieldsInSameModel()
    {
        $model = model(Project2Model::class);

        $categoryId = service('uuid')->generate('v4')->toString();

        $data = [
            'category_id' => $categoryId,
            'name'        => 'Multiple UUID Test',
            'description' => 'Multiple UUID Description',
        ];

        $id = $model->insert($data);

        $project = $model->find($id);

        $this->assertSame($categoryId, $project['category_id']);
        $this->assertSame(36, strlen((string) $project['category_id']));
    }

    public function testFindMultipleIdsWithBytesConversion()
    {
        $model = model(Project3Model::class);

        $id1 = $model->insert([
            'name'        => 'Find Multi 1',
            'description' => 'Description 1',
        ]);

        $id2 = $model->insert([
            'name'        => 'Find Multi 2',
            'description' => 'Description 2',
        ]);

        // Find should convert all IDs to bytes
        $projects = $model->find([$id1, $id2]);

        $this->assertCount(2, $projects);
        $this->assertSame($id1, $projects[0]['id']);
        $this->assertSame($id2, $projects[1]['id']);
    }

    public function testSoftDeletePreservesUuidFunctionality()
    {
        $model = model(Project1Model::class);

        $id = $model->insert([
            'name'        => 'Soft Delete UUID Test',
            'description' => 'Soft Delete UUID Description',
        ]);

        // Soft delete
        $model->delete($id);

        // Should not find without withDeleted
        $project = $model->find($id);
        $this->assertNull($project);

        // Should find with withDeleted using UUID
        $project = $model->withDeleted()->find($id);
        $this->assertIsArray($project);
        $this->assertSame($id, $project['id']);
    }

    public function testOnlyDeletedReturnsOnlySoftDeletedRecords()
    {
        $model = model(Project1Model::class);

        $model->insert([
            'name'        => 'Active Project',
            'description' => 'Active Description',
        ]);

        $id2 = $model->insert([
            'name'        => 'Deleted Project',
            'description' => 'Deleted Description',
        ]);

        $model->delete($id2);

        $projects = $model->onlyDeleted()->findAll();

        $this->assertCount(1, $projects);
        $this->assertSame($id2, $projects[0]['id']);
        $this->assertNotNull($projects[0]['deleted_at']);
    }

    public function testCustomUuidVersionV4()
    {
        $model = model(Project2Model::class);

        $data = [
            'category_id' => service('uuid')->generate('v4')->toRfc4122(),
            'name'        => 'V4 Test',
            'description' => 'V4 Description',
        ];

        $id = $model->insert($data);

        $project = $model->find($id);

        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $project['category_id']);
    }

    public function testAutoIncrementWithUuidPrimaryKeyThrowsException()
    {
        $this->expectException(CodeIgniterUuidException::class);
        $this->expectExceptionMessage('When your primary key uses UUID, the useAutoIncrement property has to be set to "false". Do not worry, the UUID will be automatically generated for the primary key.');

        // Create an anonymous model class with UUID primary key and auto-increment enabled
        new class () extends Model {
            use HasUuid;

            protected $table            = 'projects1';
            protected $primaryKey       = 'id';
            protected $useAutoIncrement = true;  // This should cause exception
            protected $returnType       = 'array';
            protected array $casts      = [
                'id' => 'uuid',
            ];
        };
    }

    public function testFirstMethodWithUuidV7OrdersByPrimaryKey()
    {
        $model = model(Project3Model::class); // Uses UUID v7 with bytes

        $model->insert([
            'name'        => 'First Project',
            'description' => 'First Description',
        ]);

        usleep(10000); // Small delay to ensure UUID v7 ordering

        $model->insert([
            'name'        => 'Second Project',
            'description' => 'Second Description',
        ]);

        usleep(10000);

        $model->insert([
            'name'        => 'Third Project',
            'description' => 'Third Description',
        ]);

        $project = $model->first();

        // With UUID v7, first() should return the oldest (first inserted)
        $this->assertIsArray($project);
        $this->assertSame('First Project', $project['name']);
        $this->assertIsString($project['id']);
    }

    public function testEmptyDataDoesNotGenerateUuid()
    {
        $model = model(Project1Model::class);

        // Insert without required fields should fail validation
        $result = $model->insert(['name' => 'a']);

        $this->assertFalse($result);

        $errors = $model->errors();
        $this->assertNotEmpty($errors);
    }

    public function testWhereInWithUuidPrimaryKey()
    {
        $model = model(Project1Model::class);

        $id1 = $model->insert([
            'name'        => 'Where In 1',
            'description' => 'Description 1',
        ]);

        $id2 = $model->insert([
            'name'        => 'Where In 2',
            'description' => 'Description 2',
        ]);

        $id3 = $model->insert([
            'name'        => 'Where In 3',
            'description' => 'Description 3',
        ]);

        $projects = $model->whereIn('id', [$id1, $id3])->findAll();

        $this->assertCount(2, $projects);
        $ids = array_column($projects, 'id');
        $this->assertContains($id1, $ids);
        $this->assertContains($id3, $ids);
        $this->assertNotContains($id2, $ids);
    }
}

<?php

declare(strict_types=1);

namespace Michalsn\CodeIgniterUuid\Traits;

use CodeIgniter\Database\RawSql;
use Michalsn\CodeIgniterUuid\Database\BinaryLiteralConverterFactory;
use Michalsn\CodeIgniterUuid\Enums\UuidType;
use Michalsn\CodeIgniterUuid\Enums\UuidVersion;
use Michalsn\CodeIgniterUuid\Exceptions\CodeIgniterUuidException;
use Michalsn\CodeIgniterUuid\Models\Cast\UuidCast;
use Symfony\Component\Uid\Uuid;

trait HasUuid
{
    /**
     * Defined UUID fields.
     */
    private array $uuidFields = [];

    /**
     * Static cache for UUID field configurations per model class.
     *
     * @var array<string, array>
     */
    private static array $uuidFieldsCache = [];

    /**
     * @return void
     */
    public function initialize()
    {
        $this->initUuid();
    }

    /**
     * Init UUID for model.
     */
    protected function initUuid(): void
    {
        $this->castHandlers = array_merge($this->castHandlers, [
            'uuid' => UuidCast::class,
        ]);

        $this->beforeInsert[]      = 'uuidBeforeInsert';
        $this->beforeInsertBatch[] = 'uuidBeforeInsertBatch';

        $this->checkUuidModelConfiguration();
    }

    /**
     * Check if the UUID configuration is set properly.
     *
     * @throws CodeIgniterUuidException
     */
    private function checkUuidModelConfiguration(): void
    {
        $this->prepareUuidFields();

        if ($this->useAutoIncrement && isset($this->uuidFields[$this->primaryKey])) {
            throw CodeIgniterUuidException::forIncorrectUseAutoIncrementValue();
        }
    }

    /**
     * Prepare UUID fields (with caching per model class).
     */
    private function prepareUuidFields(): void
    {
        $cacheKey = static::class;

        if (isset(self::$uuidFieldsCache[$cacheKey])) {
            $this->uuidFields = self::$uuidFieldsCache[$cacheKey];

            return;
        }

        $config  = config('Uuid');
        $version = $config->defaultVersion;
        $type    = $config->defaultType;

        foreach ($this->casts as $field => $cast) {
            if (str_starts_with((string) $cast, 'uuid')) {
                $fieldVersion = $version;
                $fieldType    = $type;

                if (preg_match('/uuid\[(.*?)\]/', (string) $cast, $m)) {
                    $parts = array_map(trim(...), explode(',', $m[1]));

                    if ($parts[0] !== '') {
                        $fieldVersion = UuidVersion::from($parts[0]);
                    }

                    if (isset($parts[1]) && $parts[1] !== '') {
                        $fieldType = UuidType::from($parts[1]);
                    }
                }

                $this->uuidFields[$field] = [
                    'version' => $fieldVersion,
                    'type'    => $fieldType,
                ];
            }
        }

        self::$uuidFieldsCache[$cacheKey] = $this->uuidFields;
    }

    //
    // Model Events
    //

    /**
     * Before insert event.
     */
    protected function uuidBeforeInsert(array $eventData): array
    {
        if (isset($this->uuidFields[$this->primaryKey])) {
            $eventData['data'][$this->primaryKey] = $this->createUuidValue($this->uuidFields[$this->primaryKey]);
        }

        return $eventData;
    }

    /**
     * Before insertBatch event.
     */
    protected function uuidBeforeInsertBatch(array $eventData): array
    {
        if (isset($this->uuidFields[$this->primaryKey])) {
            foreach ($eventData['data'] as &$data) {
                $data[$this->primaryKey] = $this->createUuidValue($this->uuidFields[$this->primaryKey]);
            }
        }

        return $eventData;
    }

    //
    // Helper methods
    //

    /**
     * Convert UUID primary key to bytes format if needed.
     */
    protected function convertUuidPrimaryKey(int|string $id): int|RawSql|string
    {
        if (isset($this->uuidFields[$this->primaryKey])
            && $this->uuidFields[$this->primaryKey]['type'] === UuidType::BYTES
        ) {
            $id = $this->toBinaryLiteral(Uuid::fromString($id)->toBinary(), $this->db->DBDriver);
        }

        return $id;
    }

    /**
     * Create a new UUID value.
     */
    protected function createUuidValue(array $uuidField)
    {
        $uuid = service('uuid')->generate($uuidField['version']);

        if ($uuidField['type'] === UuidType::BYTES) {
            return $this->toBinaryLiteral($uuid->toBinary(), $this->db->DBDriver);
        }

        return $uuid->toRfc4122();
    }

    /**
     * Convert raw binary to a database-specific binary literal for SQL.
     *
     * @throws CodeIgniterUuidException
     */
    private function toBinaryLiteral(string $binary, string $driver): RawSql
    {
        return BinaryLiteralConverterFactory::get($driver)->toBinaryLiteral($binary);
    }

    /**
     * Convert a database-specific binary literal back to raw binary.
     */
    private function fromBinaryLiteral(RawSql $literal, string $driver): string
    {
        return BinaryLiteralConverterFactory::get($driver)->fromBinaryLiteral($literal);
    }

    //
    // Overridden model methods
    //

    protected function shouldUpdate($row): bool
    {
        $id = $this->getIdValue($row);

        if (in_array($id, [null, [], ''], true)) {
            return false;
        }

        if ($this->useAutoIncrement === true) {
            return true;
        }

        if (is_string($id)) {
            $id = $this->convertUuidPrimaryKey($id);
        }

        return $this->where($this->primaryKey, $id)->countAllResults() === 1;
    }

    protected function doFirst()
    {
        $builder = $this->builder();

        $useCast = $this->useCasts();
        if ($useCast) {
            $returnType = $this->tempReturnType;
            $this->asArray();
        }

        if ($this->tempUseSoftDeletes) {
            $builder->where($this->table . '.' . $this->deletedField, null);
        } elseif ($this->useSoftDeletes && ($builder->QBGroupBy === []) && $this->primaryKey !== '') {
            $builder->groupBy($this->table . '.' . $this->primaryKey);
        }

        // Search when UUID6, UUID7, or ULID is used as primary key (time-sortable)
        if (empty($builder->QBOrderBy) && isset($this->uuidFields[$this->primaryKey]) && in_array($this->uuidFields[$this->primaryKey]['version'], [UuidVersion::V6, UuidVersion::V7, UuidVersion::ULID], true)) {
            $builder->orderBy($this->table . '.' . $this->primaryKey, 'asc');
        }
        // Search when a different UUID is used as a primary key
        elseif (empty($builder->QBOrderBy) && isset($this->uuidFields[$this->primaryKey]) && $this->useTimestamps === true) {
            $builder->orderBy($this->table . '.' . $this->createdField, 'asc');
        }
        // Some databases, like PostgreSQL, need order
        // information to consistently return correct results.
        elseif ($builder->QBGroupBy !== [] && ($builder->QBOrderBy === []) && $this->primaryKey !== '') {
            $builder->orderBy($this->table . '.' . $this->primaryKey, 'asc');
        }

        $row = $builder->limit(1, 0)->get()->getFirstRow($this->tempReturnType);

        if ($useCast && $row !== null) {
            $row = $this->convertToReturnType($row, $returnType);

            $this->tempReturnType = $returnType;
        }

        return $row;
    }

    protected function doInsert(array $row): bool
    {
        $result = parent::doInsert($row);

        if (isset($this->uuidFields[$this->primaryKey])
            && $this->uuidFields[$this->primaryKey]['type'] === UuidType::BYTES
            && $this->insertID instanceof RawSql) {
            $this->insertID = Uuid::fromBinary($this->fromBinaryLiteral($this->insertID, $this->db->DBDriver))->toRfc4122();
        }

        return $result;
    }

    protected function doUpdate($id = null, $row = null): bool
    {
        if (! in_array($id, [null, '', 0, '0', []], true)) {
            foreach ($id as &$val) {
                $val = $this->convertUuidPrimaryKey($val);
            }
        }

        return parent::doUpdate($id, $row);
    }

    protected function doDelete($id = null, bool $purge = false)
    {
        if (! in_array($id, [null, '', 0, '0', []], true)) {
            foreach ($id as &$val) {
                $val = $this->convertUuidPrimaryKey($val);
            }
        }

        return parent::doDelete($id, $purge);
    }

    protected function doFind(bool $singleton, $id = null)
    {
        if (is_array($id)) {
            foreach ($id as &$val) {
                $val = $this->convertUuidPrimaryKey($val);
            }
        } elseif ($singleton) {
            $id = $this->convertUuidPrimaryKey($id);
        }

        return parent::doFind($singleton, $id);
    }
}

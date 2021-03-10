<?php

namespace Michalsn\Uuid;

use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Model;
use Michalsn\Uuid\Exceptions\UuidModelException;
use Michalsn\Uuid\Uuid;

/**
 * The Uuid class Model extends a Model shipped with CodeIgniter 4.
 * This class is tightly coupled with Ramsey's Uuid class.
 */
class UuidModel extends Model
{
	/**
	 * UUID object.
	 *
	 * @var Uuid
	 */
	protected $uuid;

	/**
	 * Used UUID version.
	 * Available options: uuid1, uuid2, uuid3, uuid4, uuid5, uuid6.
	 *
	 * @var string
	 */
	protected $uuidVersion = 'uuid4';

	/**
	 * Store UUID in byte format.
	 *
	 * @var boolean
	 */
	protected $uuidUseBytes = true;

	/**
	 * UUID fields.
	 *
	 * @var array
	 */
	protected $uuidFields = ['id'];

	/**
	 * UUID temp object array.
	 *
	 * @var array
	 */
	protected $uuidTempData = [];

	//--------------------------------------------------------------------

	/**
	 * Model constructor.
	 *
	 * @param ConnectionInterface $db
	 * @param ValidationInterface $validation
	 */
	public function __construct(ConnectionInterface &$db = null, ValidationInterface $validation = null)
	{
		// We have to ensure that uuidVersion is set correctly
		if (! in_array($this->uuidVersion, config('Uuid')->supportedVersions))
		{
			throw UuidModelException::forIncorrectUuidVersion($this->uuidVersion);
		}

		// When we are not using auto-increment feature, 
		// the primary key shouldn't be in the uuidFields list
		if (! $this->useAutoIncrement && in_array($this->primaryKey, $this->uuidFields))
		{
			throw UuidModelException::forIncorrectValueInUuidFields($this->primaryKey);
		}

		// Load Uuid service
		$this->uuid = service('uuid');

		parent::__construct($db, $validation);
	}

	//--------------------------------------------------------------------
	// UUID HELPER METHODS
	//--------------------------------------------------------------------

	/**
	 * Prepare UUID results - transform if needed.
	 *
	 * @param array|object $row        Row
	 * @param string       $returnType Return type
	 *
	 * @return void;
	 */
	protected function convertUuidFieldsToStrings($results, string $returnType = 'array')
	{
		if (empty($this->uuidFields) || $this->uuidUseBytes === false)
		{
			return $results;
		}

		if (is_object($results) || empty($results[0]))//! is_array($results))
		{
			return $this->convertUuidFieldToString($results, $returnType);
		}

		foreach ($results as &$row)
		{
			$row = $this->convertUuidFieldToString($row, $returnType);
		}

		return $results;
	}

	//--------------------------------------------------------------------

	/**
	 * Prepare UUID row - transform if needed.
	 *
	 * @param array|object $row        Row
	 * @param string       $returnType Return type
	 *
	 * @return void;
	 */
	protected function convertUuidFieldToString($row, string $returnType = 'array')
	{
		if (empty($this->uuidFields) || $this->uuidUseBytes === false)
		{
			return $row;
		}

		foreach ($this->uuidFields as $field)
		{		
			if ($returnType === 'array')
			{
				if (empty($row[$field]))
				{
					continue;
				}

				$row[$field] = $this->uuid->fromBytes($row[$field])->toString();
			}
			else
			{
				if (empty($row->{$field}))
				{
					continue;
				}

				$row->{$field} = $this->uuid->fromBytes($row->{$field})->toString();
			}
		}

		return $row;
	}

	//--------------------------------------------------------------------

	/**
	 * Convert UUID primary key to bytes if needed
	 *
	 * @param array|string $key Key or array of keys to convert
	 *
	 * @return array|string
	 */
	protected function convertUuidPrimaryKeyToBytes($key = null)
	{
		if (! in_array($this->primaryKey, $this->uuidFields) || $this->uuidUseBytes === false)
		{
			return $key;
		}

		if (is_array($key))
		{
			foreach ($key as &$val)
			{
				$val = $this->uuid->fromString($val)->getBytes();
			}
		}
		elseif (! empty($key))
		{
			$key = $this->uuid->fromString($key)->getBytes();
		}

		return $key;
	}

	//--------------------------------------------------------------------

	/**
	 * Convert UUID to bytes if needed
	 *
	 * @param array $results Result array to convert
	 *
	 * @return array|string|null
	 */
	protected function convertUuidFieldsToBytes($results)
	{
		if (empty($this->uuidFields) || $this->uuidUseBytes === false)
		{
			return $results;
		}

		if (empty($results[0]))
		{
			return $this->convertUuidFieldToByte($results);
		}

		foreach ($results as &$row)
		{
			$row = $this->convertUuidFieldToByte($row);
		}

		return $results;
	}

	//--------------------------------------------------------------------

	/**
	 * Convert UUID to bytes if needed
	 *
	 * @param array $row Row array to convert
	 *
	 * @return array|string|null
	 */
	protected function convertUuidFieldToByte($row)
	{
		if (empty($this->uuidFields) || $this->uuidUseBytes === false)
		{
			return $row;
		}

		foreach ($this->uuidFields as $field)
		{
			if (empty($row[$field]))
			{
				continue;
			}

			$row[$field] = $this->uuid->fromString($row[$field])->getBytes();
		}

		return $row;
	}

	//--------------------------------------------------------------------
	// CRUD & FINDERS
	//--------------------------------------------------------------------

	/**
	 * Fetches the row of database from $this->table with a primary key
	 * matching $id.
	 *
	 * @param mixed|array|null $id One primary key or an array of primary keys
	 *
	 * @return array|object|null    The resulting row of data, or null.
	 */
	public function find($id = null)
	{
		$builder = $this->builder();

		if ($this->tempUseSoftDeletes === true)
		{
			$builder->where($this->table . '.' . $this->deletedField, null);
		}

		// Make a copy of original id
		$originalId = $id;
		// Convert UUID fields to byte if needed
		$id = $this->convertUuidPrimaryKeyToBytes($id);

		if (is_array($id))
		{
			$row = $builder->whereIn($this->table . '.' . $this->primaryKey, $id)
					->get();
			$row = $row->getResult($this->tempReturnType);
		}
		elseif (is_numeric($id) || is_string($id))
		{
			$row = $builder->where($this->table . '.' . $this->primaryKey, $id)
					->get();

			$row = $row->getFirstRow($this->tempReturnType);
		}
		else
		{
			$row = $builder->get();

			$row = $row->getResult($this->tempReturnType);
		}

		// Convert UUID fields from byte if needed
		$row = $this->convertUuidFieldsToStrings($row, $this->tempReturnType);

		$eventData = $this->trigger('afterFind', ['id' => $originalId, 'data' => $row]);

		$this->tempReturnType     = $this->returnType;
		$this->tempUseSoftDeletes = $this->useSoftDeletes;

		return $eventData['data'];
	}

	//--------------------------------------------------------------------

	/**
	 * Fetches the column of database from $this->table
	 *
	 * @param string $columnName
	 *
	 * @return array|null   The resulting row of data, or null if no data found.
	 * @throws \CodeIgniter\Database\Exceptions\DataException
	 */
	public function findColumn(string $columnName)
	{
		if (strpos($columnName, ',') !== false)
		{
			throw DataException::forFindColumnHaveMultipleColumns();
		}

		$resultSet = $this->select($columnName)
						  ->asArray()
						  ->find();

		// Convert UUID fields from byte if needed
		if (in_array($columnName, $this->uuidFields) && $this->uuidUseBytes === true)
		{
			$resultSet = $this->convertUuidFieldsToStrings($resultSet, 'array');
		}

		return (! empty($resultSet)) ? array_column($resultSet, $columnName) : null;
	}

	//--------------------------------------------------------------------

	/**
	 * Works with the current Query Builder instance to return
	 * all results, while optionally limiting them.
	 *
	 * @param integer $limit
	 * @param integer $offset
	 *
	 * @return array
	 */
	public function findAll(int $limit = 0, int $offset = 0)
	{
		$builder = $this->builder();

		if ($this->tempUseSoftDeletes === true)
		{
			$builder->where($this->table . '.' . $this->deletedField, null);
		}

		$row = $builder->limit($limit, $offset)
				->get();

		$row = $row->getResult($this->tempReturnType);

		// Convert UUID fields from byte if needed
		$row = $this->convertUuidFieldsToStrings($row, $this->tempReturnType);

		$eventData = $this->trigger('afterFind', ['data' => $row, 'limit' => $limit, 'offset' => $offset]);

		$this->tempReturnType     = $this->returnType;
		$this->tempUseSoftDeletes = $this->useSoftDeletes;

		return $eventData['data'];
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the first row of the result set. Will take any previous
	 * Query Builder calls into account when determining the result set.
	 *
	 * @return array|object|null
	 */
	public function first()
	{
		$builder = $this->builder();

		if ($this->tempUseSoftDeletes === true)
		{
			$builder->where($this->table . '.' . $this->deletedField, null);
		}
		else
		{
			if ($this->useSoftDeletes === true && empty($builder->QBGroupBy) && ! empty($this->primaryKey))
			{
				$builder->groupBy($this->table . '.' . $this->primaryKey);
			}
		}

		// Search when UUID6 is used as primary key
		if (empty($builder->QBOrderBy) && in_array($this->primaryKey, $this->uuidFields) && $this->uuidUseBytes === false && $this->uuidVersion === 'uuid6')	
		{
			$builder->orderBy($this->table . '.' . $this->primaryKey, 'asc');
		}
		// Search when other UUID is used as a primary key
		elseif (empty($builder->QBOrderBy) && in_array($this->primaryKey, $this->uuidFields) && $this->useTimestamps === true)	
		{
			$builder->orderBy($this->table . '.' . $this->createdField, 'asc');
		}

		// Some databases, like PostgreSQL, need order
		// information to consistently return correct results.
		elseif (! empty($builder->QBGroupBy) && empty($builder->QBOrderBy) && ! empty($this->primaryKey))
		{
			$builder->orderBy($this->table . '.' . $this->primaryKey, 'asc');
		}

		$row = $builder->limit(1, 0)
					   ->get();

		$row = $row->getFirstRow($this->tempReturnType);

		// Convert UUID fields from byte if needed
		$row = $this->convertUuidFieldsToStrings($row, $this->tempReturnType);

		$eventData = $this->trigger('afterFind', ['data' => $row]);

		$this->tempReturnType     = $this->returnType;
		$this->tempUseSoftDeletes = $this->useSoftDeletes;

		return $eventData['data'];
	}

	//--------------------------------------------------------------------

	/**
	 * Inserts data into the current table. If an object is provided,
	 * it will attempt to convert it to an array.
	 *
	 * @param array|object $data
	 * @param boolean      $returnID Whether insert ID should be returned or not.
	 *
	 * @return BaseResult|integer|string|false
	 * @throws \ReflectionException
	 */
	public function insert($data = null, bool $returnID = true)
	{
		$escape = null;

		$this->insertID = 0;

		if (empty($data))
		{
			$data           = $this->tempData['data'] ?? null;
			$escape         = $this->tempData['escape'] ?? null;
			$this->tempData = [];
		}

		if (empty($data))
		{
			throw DataException::forEmptyDataset('insert');
		}

		// If $data is using a custom class with public or protected
		// properties representing the table elements, we need to grab
		// them as an array.
		if (is_object($data) && ! $data instanceof stdClass)
		{
			$data = static::classToArray($data, $this->primaryKey, $this->dateFormat, false);
		}

		// If it's still a stdClass, go ahead and convert to
		// an array so doProtectFields and other model methods
		// don't have to do special checks.
		if (is_object($data))
		{
			$data = (array) $data;
		}

		if (empty($data))
		{
			throw DataException::forEmptyDataset('insert');
		}

		// Validate data before saving.
		if ($this->skipValidation === false)
		{
			if ($this->cleanRules()->validate($data) === false)
			{
				return false;
			}
		}

		// Must be called first so we don't
		// strip out created_at values.
		$data = $this->doProtectFields($data);

		// Set created_at and updated_at with same time
		$date = $this->setDate();

		if ($this->useTimestamps && ! empty($this->createdField) && ! array_key_exists($this->createdField, $data))
		{
			$data[$this->createdField] = $date;
		}

		if ($this->useTimestamps && ! empty($this->updatedField) && ! array_key_exists($this->updatedField, $data))
		{
			$data[$this->updatedField] = $date;
		}

		$eventData = ['data' => $data];
		if ($this->tempAllowCallbacks)
		{
			$eventData = $this->trigger('beforeInsert', $eventData);
		}

		// Require non empty primaryKey when
		// not using auto-increment feature
		if (! $this->useAutoIncrement && empty($eventData['data'][$this->primaryKey]))
		{
			throw DataException::forEmptyPrimaryKey('insert');
		}

		if (! empty($this->uuidFields))
		{
			foreach ($this->uuidFields as $field)
			{
				if ($field === $this->primaryKey)
				{
					$this->uuidTempData[$field] = $this->uuid->{$this->uuidVersion}();

					if ($this->uuidUseBytes === true)
					{
						$this->builder()->set($field, $this->uuidTempData[$field]->getBytes());
					}
					else
					{
						$this->builder()->set($field, $this->uuidTempData[$field]->toString());
					}
				}
				else
				{
					if ($this->uuidUseBytes === true && ! empty($eventData['data'][$field]))
					{
						$this->uuidTempData[$field] = $this->uuid->fromString($eventData['data'][$field]);
						$this->builder()->set($field, $this->uuidTempData[$field]->getBytes());
						unset($eventData['data'][$field]);
					}
				}
			}
		}

		// Must use the set() method to ensure objects get converted to arrays
		$result = $this->builder()
				->set($eventData['data'], '', $escape)
				->insert();

		// If insertion succeeded then save the insert ID
		if ($result)
		{
			if (! $this->useAutoIncrement)
			{
				$this->insertID = $eventData['data'][$this->primaryKey];
			}
			else
			{
				if (in_array($this->primaryKey, $this->uuidFields))
				{
					$this->insertID = $this->uuidTempData[$this->primaryKey]->toString();
				}
				else
				{
					$this->insertID = $this->db->insertID();
				}
			}
		}

		// Cleanup data before event trigger
		if (! empty($this->uuidFields) && $this->uuidUseBytes === true)
		{
			foreach ($this->uuidFields as $field)
			{
				if ($field === $this->primaryKey || empty($this->uuidTempData[$field]))
				{
					continue;
				}

				$eventData['data'][$field] = $this->uuidTempData[$field]->toString();
			}
		}

		$eventData = [
			'id'     => $this->insertID,
			'data'   => $eventData['data'],
			'result' => $result,
		];
		if ($this->tempAllowCallbacks)
		{
			// Trigger afterInsert events with the inserted data and new ID
			$this->trigger('afterInsert', $eventData);
		}
		$this->tempAllowCallbacks = $this->allowCallbacks;

		// If insertion failed, get out of here
		if (! $result)
		{
			return $result;
		}

		// otherwise return the insertID, if requested.
		return $returnID ? $this->insertID : $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Compiles batch insert strings and runs the queries, validating each row prior.
	 *
	 * @param array   $set       An associative array of insert values
	 * @param boolean $escape    Whether to escape values and identifiers
	 * @param integer $batchSize The size of the batch to run
	 * @param boolean $testing   True means only number of records is returned, false will execute the query
	 *
	 * @return integer|boolean Number of rows inserted or FALSE on failure
	 */
	public function insertBatch(array $set = null, bool $escape = null, int $batchSize = 100, bool $testing = false)
	{
		if (is_array($set))
		{
			foreach ($set as &$row)
			{
				// If $data is using a custom class with public or protected
				// properties representing the table elements, we need to grab
				// them as an array.
				if (is_object($row) && ! $row instanceof stdClass)
				{
					$row = static::classToArray($row, $this->primaryKey, $this->dateFormat, false);
				}

				// If it's still a stdClass, go ahead and convert to
				// an array so doProtectFields and other model methods
				// don't have to do special checks.
				if (is_object($row))
				{
					$row = (array) $row;
				}

				// Validate every row..
				if ($this->skipValidation === false && $this->cleanRules()->validate($row) === false)
				{
					return false;
				}

				// Must be called first so we don't
				// strip out created_at values.
				$row = $this->doProtectFields($row);

				// Require non empty primaryKey when
				// not using auto-increment feature
				if (! $this->useAutoIncrement && empty($row[$this->primaryKey]))
				{
					throw DataException::forEmptyPrimaryKey('insertBatch');
				}

				// Set created_at and updated_at with same time
				$date = $this->setDate();

				if ($this->useTimestamps && ! empty($this->createdField) && ! array_key_exists($this->createdField, $row))
				{
					$row[$this->createdField] = $date;
				}

				if ($this->useTimestamps && ! empty($this->updatedField) && ! array_key_exists($this->updatedField, $row))
				{
					$row[$this->updatedField] = $date;
				}

				// Add primary key and convert to bytes if needed
				if (! empty($this->uuidFields))
				{
					foreach ($this->uuidFields as $field)
					{
						if ($field === $this->primaryKey)
						{
							if ($this->uuidUseBytes === true)
							{
								$row[$field] = ($this->uuid->{$this->uuidVersion}())->getBytes();
							}
							else
							{
								$row[$field] = ($this->uuid->{$this->uuidVersion}())->toString();
							}
						}
						else
						{
							if ($this->uuidUseBytes === true && ! empty($row[$field]))
							{
								$row[$field] = ($this->uuid->fromString($row[$field]))->getBytes();
							}
						}
					}
				}
			}
		}

		return $this->builder()->testMode($testing)->insertBatch($set, $escape, $batchSize);
	}

	//--------------------------------------------------------------------

	/**
	 * Updates a single record in $this->table. If an object is provided,
	 * it will attempt to convert it into an array.
	 *
	 * @param integer|array|string $id
	 * @param array|object         $data
	 *
	 * @return boolean
	 * @throws \ReflectionException
	 */
	public function update($id = null, $data = null): bool
	{
		$escape = null;

		if (is_numeric($id) || is_string($id))
		{
			$id = [$id];
		}

		if (empty($data))
		{
			$data           = $this->tempData['data'] ?? null;
			$escape         = $this->tempData['escape'] ?? null;
			$this->tempData = [];
		}

		if (empty($data))
		{
			throw DataException::forEmptyDataset('update');
		}

		// If $data is using a custom class with public or protected
		// properties representing the table elements, we need to grab
		// them as an array.
		if (is_object($data) && ! $data instanceof stdClass)
		{
			$data = static::classToArray($data, $this->primaryKey, $this->dateFormat);
		}

		// If it's still a stdClass, go ahead and convert to
		// an array so doProtectFields and other model methods
		// don't have to do special checks.
		if (is_object($data))
		{
			$data = (array) $data;
		}

		// If it's still empty here, means $data is no change or is empty object
		if (empty($data))
		{
			throw DataException::forEmptyDataset('update');
		}

		// Validate data before saving.
		if ($this->skipValidation === false)
		{
			if ($this->cleanRules(true)->validate($data) === false)
			{
				return false;
			}
		}

		// Must be called first so we don't
		// strip out updated_at values.
		$data = $this->doProtectFields($data);

		if ($this->useTimestamps && ! empty($this->updatedField) && ! array_key_exists($this->updatedField, $data))
		{
			$data[$this->updatedField] = $this->setDate();
		}

		$eventData = [
			'id'   => $id,
			'data' => $data,
		];
		if ($this->tempAllowCallbacks)
		{
			$eventData = $this->trigger('beforeUpdate', $eventData);
		}

		$builder = $this->builder();

		// Make a copy of original id
		$originalId = $id;

		if ($id)
		{
			// Convert UUID pk to byte if needed
			$id      = $this->convertUuidPrimaryKeyToBytes($id);
			$builder = $builder->whereIn($this->table . '.' . $this->primaryKey, $id);
		}

		// Convert UUID fields if needed
		if (! empty($this->uuidFields) && $this->uuidUseBytes === true)
		{
			foreach ($this->uuidFields as $field)
			{
				if (! empty($eventData['data'][$field]))
				{
					$eventData['data'][$field] = ($this->uuid->fromString($eventData['data'][$field]))->getBytes();
				}
			}
		}

		// Must use the set() method to ensure objects get converted to arrays
		$result = $builder
				->set($eventData['data'], '', $escape)
				->update();

		// Cleanup data before event trigger
		$eventData['data'] = $this->convertUuidFieldsToStrings($eventData['data'], 'array');

		$eventData = [
			'id'     => $originalId,
			'data'   => $eventData['data'],
			'result' => $result,
		];

		if ($this->tempAllowCallbacks)
		{
			$this->trigger('afterUpdate', $eventData);
		}
		$this->tempAllowCallbacks = $this->allowCallbacks;

		return $result;
	}

	//--------------------------------------------------------------------

	/**
	 * Update_Batch
	 *
	 * Compiles an update string and runs the query
	 *
	 * @param array   $set       An associative array of update values
	 * @param string  $index     The where key
	 * @param integer $batchSize The size of the batch to run
	 * @param boolean $returnSQL True means SQL is returned, false will execute the query
	 *
	 * @return mixed    Number of rows affected or FALSE on failure
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function updateBatch(array $set = null, string $index = null, int $batchSize = 100, bool $returnSQL = false)
	{
		if (is_array($set))
		{
			foreach ($set as &$row)
			{
				// If $data is using a custom class with public or protected
				// properties representing the table elements, we need to grab
				// them as an array.
				if (is_object($row) && ! $row instanceof stdClass)
				{
					$row = static::classToArray($row, $this->primaryKey, $this->dateFormat);
				}

				// If it's still a stdClass, go ahead and convert to
				// an array so doProtectFields and other model methods
				// don't have to do special checks.
				if (is_object($row))
				{
					$row = (array) $row;
				}

				// Validate data before saving.
				if ($this->skipValidation === false && $this->cleanRules(true)->validate($row) === false)
				{
					return false;
				}

				// Save updateIndex for later
				$updateIndex = $row[$index] ?? null;

				// Must be called first so we don't
				// strip out updated_at values.
				$row = $this->doProtectFields($row);

				// Restore updateIndex value in case it was wiped out
				if ($updateIndex !== null)
				{
					$row[$index] = $updateIndex;
				}

				if ($this->useTimestamps && ! empty($this->updatedField) && ! array_key_exists($this->updatedField, $row))
				{
					$row[$this->updatedField] = $this->setDate();
				}

				// Convert UUID fields if needed
				if (! empty($this->uuidFields) && $this->uuidUseBytes === true)
				{
					foreach ($this->uuidFields as $field)
					{
						if (! empty($row[$field]))
						{
							$row[$field] = ($this->uuid->fromString($row[$field]))->getBytes();
						}
					}
				}
			}
		}

		return $this->builder()->testMode($returnSQL)->updateBatch($set, $index, $batchSize);
	}

	//--------------------------------------------------------------------

	/**
	 * Deletes a single record from $this->table where $id matches
	 * the table's primaryKey
	 *
	 * @param integer|string|array|null $id    The rows primary key(s)
	 * @param boolean                   $purge Allows overriding the soft deletes setting.
	 *
	 * @return BaseResult|boolean
	 * @throws \CodeIgniter\Database\Exceptions\DatabaseException
	 */
	public function delete($id = null, bool $purge = false)
	{
		if (! empty($id) && (is_numeric($id) || is_string($id)))
		{
			$id = [$id];
		}

		// Make a copy of original id
		$originalId = $id;

		$builder = $this->builder();

		if (! empty($id))
		{
			// Convert UUID pk to byte if needed
			$id      = $this->convertUuidPrimaryKeyToBytes($id);
			$builder = $builder->whereIn($this->primaryKey, $id);
		}

		$eventData = [
			'id'    => $originalId,
			'purge' => $purge,
		];
		if ($this->tempAllowCallbacks)
		{
			$this->trigger('beforeDelete', $eventData);
		}

		if ($this->useSoftDeletes && ! $purge)
		{
			if (empty($builder->getCompiledQBWhere()))
			{
				if (CI_DEBUG)
				{
					throw new DatabaseException('Deletes are not allowed unless they contain a "where" or "like" clause.');
				}
				// @codeCoverageIgnoreStart
				return false;
				// @codeCoverageIgnoreEnd
			}
			$set[$this->deletedField] = $this->setDate();

			if ($this->useTimestamps && ! empty($this->updatedField))
			{
				$set[$this->updatedField] = $this->setDate();
			}

			$result = $builder->update($set);
		}
		else
		{
			$result = $builder->delete();
		}

		$eventData = [
			'id'     => $originalId,
			'purge'  => $purge,
			'result' => $result,
			'data'   => null,
		];
		if ($this->tempAllowCallbacks)
		{
			$this->trigger('afterDelete', $eventData);
		}
		$this->tempAllowCallbacks = $this->allowCallbacks;

		return $result;
	}

}
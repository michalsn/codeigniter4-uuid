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
	 * matching $id. This methods works only with dbCalls
	 * This methods works only with dbCalls
	 *
	 * @param boolean                   $singleton Single or multiple results
	 * @param array|integer|string|null $id        One primary key or an array of primary keys
	 *
	 * @return array|object|null    The resulting row of data, or null.
	 */
	protected function doFind(bool $singleton, $id = null)
	{
		// Convert UUID fields to byte if needed
		$id = $this->convertUuidPrimaryKeyToBytes($id);
		
		$result = parent::doFind($singleton, $id);
		// Convert UUID fields from byte if needed
		$result = $this->convertUuidFieldsToStrings($result, $this->tempReturnType);

		return $result;
	}

	/**
	 * Fetches the column of database from $this->table
	 * This methods works only with dbCalls
	 *
	 * @param string $columnName Column Name
	 *
	 * @return array|null The resulting row of data, or null if no data found.
	 */
	protected function doFindColumn(string $columnName)
	{
		$result = parent::doFindColumn($columnName);
		
		// Convert UUID fields from byte if needed
		if (in_array($columnName, $this->uuidFields) && $this->uuidUseBytes === true)
		{
			$result = $this->convertUuidFieldsToStrings($result, 'array');
		}

		return $result;
	}

	/**
	 * Works with the current Query Builder instance to return
	 * all results, while optionally limiting them.
	 * This methods works only with dbCalls
	 *
	 * @param integer $limit  Limit
	 * @param integer $offset Offset
	 *
	 * @return array
	 */
	protected function doFindAll(int $limit = 0, int $offset = 0)
	{
		$result = parent::doFindAll($limit, $offset);
		// Convert UUID fields from byte if needed
		$result = $this->convertUuidFieldsToStrings($result, $this->tempReturnType);

		return $result;
	}

	/**
	 * Returns the first row of the result set. Will take any previous
	 * Query Builder calls into account when determining the result set.
	 * This methods works only with dbCalls
	 *
	 * @return array|object|null
	 */
	protected function doFirst()
	{
		$builder = $this->builder();

		if ($this->tempUseSoftDeletes)
		{
			$builder->where($this->table . '.' . $this->deletedField, null);
		}
		elseif ($this->useSoftDeletes && empty($builder->QBGroupBy) && $this->primaryKey)
		{
			$builder->groupBy($this->table . '.' . $this->primaryKey);
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
		elseif ($builder->QBGroupBy && empty($builder->QBOrderBy) && $this->primaryKey)
		{
			$builder->orderBy($this->table . '.' . $this->primaryKey, 'asc');
		}

		$result =  $builder->limit(1, 0)->get()->getFirstRow($this->tempReturnType);
		
		// Convert UUID fields from byte if needed
		return $this->convertUuidFieldsToStrings($result, $this->tempReturnType);
	}

	/**
	 * Inserts data into the current table.
	 * This methods works only with dbCalls
	 *
	 * @param array $data Data
	 *
	 * @return Query|boolean
	 */
	protected function doInsert(array $data)
	{
		$escape       = $this->escape;
		$this->escape = [];

		// Require non empty primaryKey when
		// not using auto-increment feature
		if (! $this->useAutoIncrement && empty($data[$this->primaryKey]))
		{
			throw DataException::forEmptyPrimaryKey('insert');
		}

		$builder = $this->builder();

		if (! empty($this->uuidFields))
		{
			if (in_array($this->primaryKey, $this->uuidFields) && empty($data[$this->primaryKey]))
			{
				$primaryVal = $this->uuid->{$this->uuidVersion}();

				if ($this->uuidUseBytes === true)
				{
					$builder->set($this->primaryKey, $primaryVal->getBytes());
				}
				else
				{
					$builder->set($this->primaryKey, $primaryVal->toString());
				}
			}
		}		

		// Must use the set() method to ensure to set the correct escape flag
		foreach ($data as $key => $val)
		{
			// Convert UUID fields if needed
			if ($val && in_array($key, $this->uuidFields) && $this->uuidUseBytes === true)
			{
				$val = ($this->uuid->fromString($val))->getBytes();
			}
			
			$builder->set($key, $val, $escape[$key] ?? null);
		}

		$result = $builder->insert();

		// If insertion succeeded then save the insert ID
		if ($result)
		{
			if (! $this->useAutoIncrement)
			{
				$this->insertID = $data[$this->primaryKey];
			}
			else
			{
				if (in_array($this->primaryKey, $this->uuidFields))
				{
					$this->insertID = empty($primaryVal) ? $data[$this->primaryKey] : $primaryVal->toString();
				}
				else
				{
					$this->insertID = $this->db->insertID();
				}
			}
		}

		return $result;
	}

	/**
	 * Compiles batch insert strings and runs the queries, validating each row prior.
	 * This methods works only with dbCalls
	 *
	 * @param array|null   $set       An associative array of insert values
	 * @param boolean|null $escape    Whether to escape values and identifiers
	 * @param integer      $batchSize The size of the batch to run
	 * @param boolean      $testing   True means only number of records is returned, false will execute the query
	 *
	 * @return integer|boolean Number of rows inserted or FALSE on failure
	 */
	protected function doInsertBatch(?array $set = null, ?bool $escape = null, int $batchSize = 100, bool $testing = false)
	{
		if (is_array($set))
		{
			foreach ($set as &$row)
			{
				// Require non empty primaryKey when
				// not using auto-increment feature
				if (! $this->useAutoIncrement && empty($row[$this->primaryKey]))
				{
					throw DataException::forEmptyPrimaryKey('insertBatch');
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

	/**
	 * Updates a single record in $this->table.
	 * This methods works only with dbCalls
	 *
	 * @param integer|array|string|null $id   ID
	 * @param array|null                $data Data
	 *
	 * @return boolean
	 */
	protected function doUpdate($id = null, $data = null): bool
	{
		$escape       = $this->escape;
		$this->escape = [];

		$builder = $this->builder();

		if ($id)
		{
			$id      = $this->convertUuidPrimaryKeyToBytes($id);
			$builder = $builder->whereIn($this->table . '.' . $this->primaryKey, $id);
		}

		// Must use the set() method to ensure to set the correct escape flag
		foreach ($data as $key => $val)
		{
			// Convert UUID fields if needed
			if ($val && in_array($key, $this->uuidFields) && $this->uuidUseBytes === true)
			{
				$val = ($this->uuid->fromString($val))->getBytes();
			}

			$builder->set($key, $val, $escape[$key] ?? null);
		}

		return $builder->update();
	}

	/**
	 * Compiles an update string and runs the query
	 * This methods works only with dbCalls
	 *
	 * @param array|null  $set       An associative array of update values
	 * @param string|null $index     The where key
	 * @param integer     $batchSize The size of the batch to run
	 * @param boolean     $returnSQL True means SQL is returned, false will execute the query
	 *
	 * @return mixed    Number of rows affected or FALSE on failure
	 *
	 * @throws DatabaseException
	 */
	protected function doUpdateBatch(array $set = null, string $index = null, int $batchSize = 100, bool $returnSQL = false)
	{
		foreach ($set as &$row)
		{
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

		return $this->builder()->testMode($returnSQL)->updateBatch($set, $index, $batchSize);
	}

	/**
	 * Deletes a single record from $this->table where $id matches
	 * the table's primaryKey
	 * This methods works only with dbCalls
	 *
	 * @param integer|string|array|null $id    The rows primary key(s)
	 * @param boolean                   $purge Allows overriding the soft deletes setting.
	 *
	 * @return string|boolean
	 *
	 * @throws DatabaseException
	 */
	protected function doDelete($id = null, bool $purge = false)
	{
		// Convert UUID pk to byte if needed
		$id = $this->convertUuidPrimaryKeyToBytes($id);
		return parent::doDelete($id, $purge);
	}

}